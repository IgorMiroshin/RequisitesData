<?php
header("Content-type: application/json; charset=utf-8");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

class RequisitesData
{
    public int $userID;
    public int $requisitesID;

    public function __construct(int $userID, int $requisitesID)
    {
        $this->userID = $userID;
        $this->requisitesID = $requisitesID;
    }

    public function delete(): array
    {
        $result = [];
        $userID = $this->userID;
        $requisitesID = $this->requisitesID;
        $userClass = new CUser;

        if (!\Bitrix\Main\Loader::includeModule('iblock')) {
            return [];
        }

        $userArray = CUser::GetByID($userID)->GetNext();
        $requisitesArray = $userArray["UF_REQUISITES"];

        foreach ($requisitesArray as $key => $requisitesArrayItem) {
            if ((int)$requisitesArrayItem === $requisitesID) {
                $resultDelete = CIBlockElement::Delete($requisitesArrayItem);
                if ($resultDelete) {
                    $result["success"] = 'Реквизит успешно удален';
                    unset($requisitesArray[$key]);
                    $userClass->Update($userID, ["UF_REQUISITES" => $requisitesArray]);
                } else {
                    $result["errors"]["delete"] = 'Ошибка удаления!';
                }
            }
        }

        return $result;
    }
}

$result = [];

$requisitesID = $_POST["USER_REQUISITES_ID"];
$userID = $_POST["USER_ID"];

if (!empty($requisitesID) && !empty($userID)) {
    $requisitesDataClass = new RequisitesData($userID, $requisitesID);
    $result = $requisitesDataClass->delete();
} else {
    if (empty($requisitesID)) {
        $result["errors"]["product"] = 'Не найден удаляемый реквизит!';
    }
    if (empty($userID)) {
        $result["errors"]['user'] = 'Не найден такой пользователь!';
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);