<?php

class RestMuseum
{
    public $method;
    public $get;
    public $post;

    const IBLOCK_ID_EVENTS = 10;
    const IBLOCK_ID_NEWS = 2;
    const IBLOCK_ID_PRODUCT = 11;
    const IBLOCK_ID_COLLECTION = 19;
    const IBLOCK_ID_PHOTO = 20;
    const IBLOCK_ID_VIDEO = 21;


    public function __construct($url = '', $get = [], $post = [])
    {
        $this->method = $this->GetMethod($url);
        $this->get = $get;
        $this->post = $post;
    }

    private function GetMethod(string $url): string
    {
        $arURL = parse_url($url);
        $arPATH = explode("/", $arURL["path"]);
        return end($arPATH);
    }

    public function GetData(): array
    {
        switch ($this->method) {
            case "events":
                return $this->GetEventsList();
            case "news":
                return $this->GetNewsList();
            case "shop":
                return $this->GetProductList();
            case "collection":
                return $this->GetCollectionList();
            case "gallery-photo":
                return $this->GetGalleryPhotoList();
            case "gallery-video":
                return $this->GetGalleryVideoList();
            case "subscription":
                return $this->SetSubscription();
            default:
                return [];
        }
    }

    private function GetEventsList(): array
    {
        $data = [];
        $data["items"] = [];
        $data["pages"] = 0;
        $get = $this->get;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $countElement = $this->GetCountElements(self::IBLOCK_ID_EVENTS);
        $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

        if ($countPageNav >= $get["_page"]) {
            $arSort = ["ACTIVE_FROM" => "ASC", "SORT" => "ASC"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_EVENTS, "INCLUDE_SUBSECTIONS" => "Y"];
            $arSelect = [
                "ID",
                "ACTIVE_FROM",
                "NAME",
                "IBLOCK_SECTION_ID",
                "PREVIEW_TEXT",
                "PREVIEW_PICTURE",
                "DETAIL_PAGE_URL",
                "PROPERTY_LOCATION",
                "PROPERTY_PRICE",
                "PROPERTY_TYPE_EVENTS",
                "PROPERTY_LINK"
            ];

            if (!empty($get["_date"])) {
                $arFilter[">=DATE_ACTIVE_FROM"] = $get["_date"];
                $arFilter["<=DATE_ACTIVE_FROM"] = date("d.m.Y", strtotime("+1 day", strtotime($get["_date"])));
            }

            if (!empty($get["_cat"])) {
                $arFilter["SECTION_ID"] = $get["_cat"];
            }

            $arNavStartParams = !empty($get["_page"]) ? ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]] : false;

            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $section = CIBlockSection::GetByID($item["IBLOCK_SECTION_ID"])->GetNext();

                $data["items"][] = [
                    "id" => $item["ID"],
                    "datetime" => strtotime($item["ACTIVE_FROM"]) * 1000,
                    "title" => $item["NAME"],
                    "description" => TruncateText($item["PREVIEW_TEXT"], 120),
                    "preview" => $this->GetPicturePath($item["PREVIEW_PICTURE"], 370, 280),
                    "linkPost" => $item["DETAIL_PAGE_URL"],
                    "location" => $item["PROPERTY_LOCATION_VALUE"],
                    "price" => $item["PROPERTY_PRICE_VALUE"],
                    "cat" => $section["NAME"],
                    "link" => $item["PROPERTY_LINK_VALUE"]
                ];
            }
        }

        if (!empty($data["items"])) {
            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }

        return $data;
    }

    private function GetNewsList(): array
    {
        $data = [];
        $data["items"] = [];
        $data["pages"] = 0;
        $get = $this->get;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $countElement = $this->GetCountElements(self::IBLOCK_ID_NEWS);
        $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

        if ($countPageNav >= $get["_page"]) {
            $arSort = ["ACTIVE_FROM" => "DESC", "SORT" => "ASC"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_NEWS, "INCLUDE_SUBSECTIONS" => "Y"];
            $arSelect = [
                "ID",
                "NAME",
                "ACTIVE_FROM",
                "PREVIEW_TEXT",
                "PREVIEW_PICTURE",
                "DETAIL_PAGE_URL"
            ];

            if (!empty($get["_date"])) {
                $arFilter[">=DATE_ACTIVE_FROM"] = $get["_date"];
                $arFilter["<=DATE_ACTIVE_FROM"] = date("d.m.Y", strtotime("+1 day", strtotime($get["_date"])));
            }

            $arNavStartParams = !empty($get["_page"]) ? ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]] : false;

            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][] = [
                    "id" => $item["ID"],
                    "datetime" => strtotime($item["ACTIVE_FROM"]) * 1000,
                    "title" => $item["NAME"],
                    "description" => TruncateText($item["PREVIEW_TEXT"], 220),
                    "preview" => !empty($item["PREVIEW_PICTURE"]) ? $this->GetPicturePath($item["PREVIEW_PICTURE"], 370, 280) : '',
                    "linkPost" => $item["DETAIL_PAGE_URL"]
                ];
            }
        }

        if (!empty($data["items"])) {
            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }

        return $data;
    }

    private function GetProductList(): array
    {
        $data = [];
        $data["items"] = [];
        $data["pages"] = 0;
        $get = $this->get;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $countElement = $this->GetCountElements(self::IBLOCK_ID_PRODUCT);
        $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

        if ($countPageNav >= $get["_page"]) {
            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_PRODUCT, "INCLUDE_SUBSECTIONS" => "Y"];
            $arSelect = [
                "ID",
                "NAME",
                "PREVIEW_TEXT",
                "PREVIEW_PICTURE",
                "DETAIL_PICTURE",
                "DETAIL_TEXT",
                "PROPERTY_PRICE",
                "SECTION_ID"
            ];

            if (!empty($get["_cat"])) {
                $arFilter["SECTION_ID"] = $get["_cat"];
            }

            $arNavStartParams = !empty($get["_page"]) ? ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]] : false;

            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][] = [
                    "id" => $item["ID"],
                    "title" => $item["NAME"],
                    "description" => $item["PREVIEW_TEXT"],
                    "content" => $item["DETAIL_TEXT"],
                    "preview" => $this->GetPicturePath($item["PREVIEW_PICTURE"], 370, 370),
                    "images" => CFile::GetPath($item["DETAIL_PICTURE"]),
                    "price" => $item["PROPERTY_PRICE_VALUE"],
                    "cat" => $item["SECTION_ID"]
                ];
            }
        }

        if (!empty($data["items"])) {
            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }

        return $data;
    }

    private function GetCollectionList(): array
    {
        $data = [];
        $data["items"] = [];
        $data["pages"] = 0;
        $get = $this->get;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $countElement = $this->GetCountElements(self::IBLOCK_ID_COLLECTION);
        $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

        if ($countPageNav >= $get["_page"]) {
            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_COLLECTION, "INCLUDE_SUBSECTIONS" => "Y"];
            $arSelect = [
                "ID",
                "NAME",
                "PREVIEW_TEXT",
                "DETAIL_TEXT",
                "PREVIEW_PICTURE",
                "DETAIL_PICTURE",
                "SECTION_ID"
            ];

            if (!empty($get["_id"])) {
                $arFilter["SECTION_ID"] = $get["_id"];
            }

            $arNavStartParams = !empty($get["_page"]) ? ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]] : false;

            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][] = [
                    "id" => $item["ID"],
                    "title" => $item["NAME"],
                    "description" => TruncateText($item["PREVIEW_TEXT"], 220),
                    "preview" => $this->GetPicturePath($item["PREVIEW_PICTURE"], 260, 320),
                    "thumb" => !empty($item["DETAIL_PICTURE"]) ? CFile::GetPath($item["DETAIL_PICTURE"]) : CFile::GetPath($item["PREVIEW_PICTURE"])

                ];
            }
        }
        if (!empty($data["items"])) {
            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }

        return $data;
    }

    private function GetGalleryPhotoList(): array
    {
        $data = [];
        $data["items"] = [];
        $data["pages"] = 0;
        $get = $this->get;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $countElement = $this->GetCountElements(self::IBLOCK_ID_PHOTO);
        $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

        if ($countPageNav >= $get["_page"]) {
            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_PHOTO, "INCLUDE_SUBSECTIONS" => "Y"];
            $arSelect = [
                "ID",
                "NAME",
                "PREVIEW_TEXT",
                "PREVIEW_PICTURE",
                "DETAIL_PICTURE",
                "PROPERTY_LINK"
            ];

            $arNavStartParams = !empty($get["_page"]) ? ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]] : false;

            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][] =
                    [
                        "id" => $item["ID"],
                        "title" => $item["NAME"],
                        "description" => $item["PREVIEW_TEXT"],
                        "content" => $item["DETAIL_TEXT"],
                        "preview" => $this->GetPicturePath($item["PREVIEW_PICTURE"], 270, 250),
                        "images" => CFile::GetPath($item["DETAIL_PICTURE"]),
                        "frame" => $item["PROPERTY_LINK_VALUE"]
                    ];
            }
        }

        if (!empty($data["items"])) {
            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }

        return $data;
    }

    private function GetGalleryVideoList(): array
    {
        $data = [];
        $data["items"] = [];
        $data["pages"] = 0;
        $get = $this->get;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $countElement = $this->GetCountElements(self::IBLOCK_ID_VIDEO);
        $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

        if ($countPageNav >= $get["_page"]) {
            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => self::IBLOCK_ID_VIDEO, "INCLUDE_SUBSECTIONS" => "Y"];
            $arSelect = [
                "ID",
                "NAME",
                "PREVIEW_TEXT",
                "PREVIEW_PICTURE",
                "DETAIL_PICTURE",
                "PROPERTY_LINK"
            ];

            $arNavStartParams = !empty($get["_page"]) ? ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]] : false;

            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][] =
                    [
                        "id" => $item["ID"],
                        "title" => $item["NAME"],
                        "description" => $item["PREVIEW_TEXT"],
                        "content" => $item["DETAIL_TEXT"],
                        "preview" => $this->GetPicturePath($item["PREVIEW_PICTURE"], 270, 250),
                        "images" => CFile::GetPath($item["DETAIL_PICTURE"]),
                        "frame" => $item["PROPERTY_LINK_VALUE"]
                    ];
            }
        }

        if (!empty($data["items"])) {
            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }

        return $data;
    }

    private function SetSubscription()
    {
        $data = [];
        $post = $this->post;

        if (!\Bitrix\Main\Loader::includeModule('form')) {
            return [];
        }

        if (!empty($post["email"])) {
            $FORM_ID = 1;
            $FIELDS = [
                "form_text_1" => $post["email"],
            ];

            if ($result = CFormResult::Add($FORM_ID, $FIELDS)) {
                if (CFormResult::Mail($result)) {
                    $data["status"] = "Code: 200 OK";
                } else {
                    $data["status"] = "Code: 400 BAD REQUEST";
                }
            } else {
                $data["status"] = "Code: 400 BAD REQUEST";
            }
        }

        return $data;
    }

    private function GetCountElements(int $IBLOCK_ID): int
    {
        CModule::IncludeModule("iblock");

        $get = $this->get;

        $arFilter = ["IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y"];

        if (!empty($get["_date"])) {
            $arFilter += [">=DATE_ACTIVE_FROM" => $get["_date"], "<=DATE_ACTIVE_FROM" => date("d.m.Y", strtotime("+1 day", strtotime($get["_date"])))];
        }

        if (!empty($get["_cat"])) {
            $arFilter += ["SECTION_ID" => $get["_cat"]];
        }
        return CIBlockElement::GetList([], $arFilter, [], false, []);
    }

    private function GetCountPageNav(int $countElement, int $limit): int
    {
        return ceil($countElement / $limit);
    }

    private function GetPicturePath(int $imageID, int $width, int $height): string
    {
        CModule::IncludeModule("iblock");
        $imgData = CFile::GetByID($imageID)->GetNext();
        $img = CFile::ResizeImageGet($imgData, ["width" => $width, "height" => $height], BX_RESIZE_IMAGE_EXACT, true);
        $imgSrc = !empty($img["src"]) ? $img["src"] : CFile::GetPath($imageID);

        return $imgSrc;
    }
}