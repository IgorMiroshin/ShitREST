<?php

class RestMuseum
{
    public function __construct(array $data = [])
    {
        $link = $data['url'];
        if (!empty($link)) {
            $arURL = parse_url($link);
            if (!empty($arURL["path"])) {
                $arPATH = explode("/", $arURL["path"]);
                $this->method = end($arPATH);
            }
            if (!empty($arURL["query"])) {
                $arQuery = [];
                $arQueryExplode = explode("&", $arURL["query"]);
                foreach ($arQueryExplode as $arQueryExplodeItem) {
                    $arQueryExplodeItemExplode = explode("=", $arQueryExplodeItem);
                    $arQuery[$arQueryExplodeItemExplode[0]] = $arQueryExplodeItemExplode[1];
                }
                $this->params = $arQuery;
            }
        }
        if (!empty($data['get'])) {
            $this->get = $data['get'];
        }

        if (!empty($data['post'])) {
            $this->post = $data['post'];
        }
    }

    public function GetData(): array
    {
        $data = [];
        $method = $this->method;

        switch ($method) {
            case "events":
                $data = $this->GetEventsList();
                break;
            case "news":
                $data = $this->GetNewsList();
                break;
            case "shop":
                $data = $this->GetProductList();
                break;
            case "collection":
                $data = $this->GetCollectionList();
                break;
            case "gallery":
                $data = $this->GetGalleryList();
                break;
            case "subscription":
                $data = $this->SetSubscription();
                break;
        }
        return $data;
    }

    private function GetEventsList(): array
    {
        $data = [];
        $get = $this->get;

        if (CModule::IncludeModule("iblock")) {
            $IBLOCK_ID = 10;

            $arSort = ["ACTIVE_FROM" => "ASC", "SORT" => "ASC"];
            $arFilter = ["IBLOCK_ID" => $IBLOCK_ID];

            if (!empty($get["_date"])) {
                $arFilter += ["ACTIVE_FROM" => $get["_date"]];
            }

            if (!empty($get["_cat"])) {
                $arFilter += ["PROPERTY_TYPE_EVENTS_VALUE" => $get["_cat"]];
            }

            $arNavStartParams = false;

            if (!empty($get["_page"])) {
                $arNavStartParams = ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]];
            }

            $key = 0;
            $arSelect = ["ID", "ACTIVE_FROM", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PAGE_URL", "PROPERTY_LOCATION", "PROPERTY_PRICE", "PROPERTY_TYPE_EVENTS", "PROPERTY_LINK"];
            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
     
                $data["items"][$key]["id"] = $item["ID"];
                $data["items"][$key]["datetime"] = strtotime($item["ACTIVE_FROM"]);
                $data["items"][$key]["title"] = $item["NAME"];
                $data["items"][$key]["description"] = $item["PREVIEW_TEXT"];
                $data["items"][$key]["preview"] = CFile::GetPath($item["PREVIEW_PICTURE"]);
                $data["items"][$key]["linkPost"] = $item["DETAIL_PAGE_URL"];

                $data["items"][$key]["location"] = $item["PROPERTY_LOCATION_VALUE"];
                $data["items"][$key]["price"] = $item["PROPERTY_PRICE_VALUE"];
                $data["items"][$key]["cat"] = $item["PROPERTY_TYPE_EVENTS_VALUE"];
                $data["items"][$key]["link"] = $item["PROPERTY_LINK_VALUE"];

                $key++;
            }

            $countElement = $this->GetCountElements($IBLOCK_ID);
            $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }
        return $data;
    }

    private function GetNewsList(): array
    {
        $data = [];
        $get = $this->get;

        if (CModule::IncludeModule("iblock")) {
            $IBLOCK_ID = 2;

            $arSort = ["ACTIVE_FROM" => "DESC", "SORT" => "ASC"];
            $arFilter = ["IBLOCK_ID" => $IBLOCK_ID];
            $arSelect = ["ID", "NAME", "ACTIVE_FROM", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PAGE_URL"];

            if (!empty($get["_date"])) {
                $arFilter += ["ACTIVE_FROM" => $get["_date"]];
            }

            $arNavStartParams = false;

            if (!empty($get["_page"])) {
                $arNavStartParams = ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]];
            }

            $key = 0;
            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][$key]["id"] = $item["ID"];
                $data["items"][$key]["datetime"] = strtotime($item["ACTIVE_FROM"]);
                $data["items"][$key]["title"] = $item["NAME"];
                $data["items"][$key]["description"] = $item["PREVIEW_TEXT"];
                $data["items"][$key]["preview"] = CFile::GetPath($item["PREVIEW_PICTURE"]);
                $data["items"][$key]["linkPost"] = $item["DETAIL_PAGE_URL"];

                $key++;
            }

            $countElement = $this->GetCountElements($IBLOCK_ID);
            $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }
        return $data;
    }

    private function GetProductList(): array
    {
        $data = [];
        $get = $this->get;

        if (CModule::IncludeModule("iblock")) {
            $IBLOCK_ID = 11;

            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => $IBLOCK_ID];
            $arSelect = ["ID", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DETAIL_TEXT", "PROPERTY_PRICE", "SECTION_ID"];

            if (!empty($get["_cat"])) {
                $arFilter += ["SECTION_ID" => $get["_cat"]];
            }

            $arNavStartParams = false;

            if (!empty($get["_page"])) {
                $arNavStartParams = ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]];
            }

            $key = 0;
            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][$key]["id"] = $item["ID"];
                $data["items"][$key]["title"] = $item["NAME"];
                $data["items"][$key]["description"] = $item["PREVIEW_TEXT"];
                $data["items"][$key]["content"] = $item["DETAIL_TEXT"];
                $data["items"][$key]["preview"] = CFile::GetPath($item["PREVIEW_PICTURE"]);
                $data["items"][$key]["images"] = CFile::GetPath($item["DETAIL_PICTURE"]);

                $data["items"][$key]["price"] = $item["PROPERTY_PRICE_VALUE"];
                $data["items"][$key]["cat"] = $item["SECTION_ID"];

                $key++;
            }

            $countElement = $this->GetCountElements($IBLOCK_ID);
            $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }
        return $data;
    }

    private function GetCollectionList(): array
    {
        $data = [];
        $get = $this->get;

        if (CModule::IncludeModule("iblock")) {
            $IBLOCK_ID = 19;

            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => $IBLOCK_ID];
            $arSelect = ["ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE", "SECTION_ID"];

            $arNavStartParams = false;

            if (!empty($get["_page"])) {
                $arNavStartParams = ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]];
            }

            $key = 0;
            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][$key]["id"] = $item["ID"];
                $data["items"][$key]["title"] = $item["NAME"];
                $data["items"][$key]["description"] = $item["PREVIEW_TEXT"];
                $data["items"][$key]["content"] = $item["DETAIL_TEXT"];
                $data["items"][$key]["preview"] = CFile::GetPath($item["PREVIEW_PICTURE"]);
                $data["items"][$key]["images"] = CFile::GetPath($item["DETAIL_PICTURE"]);
                $data["items"][$key]["cat"] = $item["SECTION_ID"];

                $key++;
            }

            $countElement = $this->GetCountElements($IBLOCK_ID);
            $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }
        return $data;
    }

    private function GetGalleryList(): array
    {
        $data = [];
        $get = $this->get;

        if (CModule::IncludeModule("iblock")) {
            $IBLOCK_ID = $get["_type"] === 'video' ? 21 : 20;

            $arSort = ["SORT" => "ASC", "NAME" => "ASC"];
            $arFilter = ["IBLOCK_ID" => $IBLOCK_ID];
            $arSelect = ["ID", "NAME", "PREVIEW_TEXT", "PREVIEW_PICTURE", "DETAIL_PICTURE", "PROPERTY_LINK"];

            $arNavStartParams = false;

            if (!empty($get["_page"])) {
                $arNavStartParams = ["iNumPage" => $get["_page"], "nPageSize" => $get["_limit"]];
            }

            $key = 0;
            $itemGetList = CIBlockElement::GetList($arSort, $arFilter, false, $arNavStartParams, $arSelect);
            while ($item = $itemGetList->GetNext()) {
                $data["items"][$key]["id"] = $item["ID"];
                $data["items"][$key]["title"] = $item["NAME"];
                $data["items"][$key]["description"] = $item["PREVIEW_TEXT"];
                $data["items"][$key]["content"] = $item["DETAIL_TEXT"];
                $data["items"][$key]["preview"] = CFile::GetPath($item["PREVIEW_PICTURE"]);
                $data["items"][$key]["images"] = CFile::GetPath($item["DETAIL_PICTURE"]);
                $data["items"][$key]["frame"] = $item["PROPERTY_LINK_VALUE"];
            }

            $countElement = $this->GetCountElements($IBLOCK_ID);
            $countPageNav = $this->GetCountPageNav($countElement, $get["_limit"]);

            header("X-Total-Count: " . $countPageNav);
            $data["pages"] = $countPageNav;
        }
        return $data;
    }

    private function SetSubscription()
    {
        $data = [];
        $post = $this->post;

        if (CModule::IncludeModule("form")) {

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
        }
        return $data;
    }

    private function GetCountElements(int $IBLOCK_ID): int
    {
        CModule::IncludeModule("iblock");
        return CIBlockElement::GetList([], ["IBLOCK_ID" => $IBLOCK_ID, "ACTIVE" => "Y"], [], false, []);
    }

    private function GetCountPageNav(int $countElement, int $limit): int
    {
        return round($countElement / $limit);
    }
}