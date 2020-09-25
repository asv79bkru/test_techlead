<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();

use \Bitrix\Main\UserGroupTable, \Bitrix\Main\UserTable, \Bitrix\Main\Application;

class PresalesEdit extends CBitrixComponent
{

    public function executeComponent()
    {
        $structure = CIntranetUtils::getStructure();
        $arDepartments = $structure['DATA'];
        $arHeadDepartment = [];
        $arEmployeesDepartment = [];
        $arDepartmentName = [];
        $this->arResult['STATUS_ERR'] = '';

        //Получаем id департамента через request
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();
        $requestDepartmentId = (int)$request['departmentid'];
        if (!empty($requestDepartmentId)) {

            //Получаем массив руководителей подразделений
            if (!empty($arDepartments)) {
                $arHeadDepartment = array_column($arDepartments, 'UF_HEAD', 'ID');
                $arEmployeesDepartment = array_column($arDepartments, 'EMPLOYEES', 'ID');
                $arDepartmentName = array_column($arDepartments, 'NAME', 'ID');
            }

            global $USER;
            if ((in_array($USER->GetID(), $this->arParams['ADMIN_USER_ID'])) || (in_array($USER->GetID(), $arHeadDepartment))) {

                $arFilterForSearch = [
                    'ACTIVE' => 'Y',
                ];

                $arFilterForPresales = [
                    'GROUP_ID'    => $this->arParams['USER_GROUP'],
                    'USER.ACTIVE' => 'Y',
                ];

                if (in_array($USER->GetID(), $arHeadDepartment)
                    && (!in_array($USER->GetID(), $this->arParams['ADMIN_USER_ID']))) {
                    $idDepartment = $arHeadDepartment[$requestDepartmentId] == $USER->GetID() ? $requestDepartmentId : '';
                    if (!empty($idDepartment) && !empty($arEmployeesDepartment[$idDepartment])) {
                        $arFilterForPresales['=USER_ID'] = $arEmployeesDepartment[$idDepartment];
                        $this->arResult['DEPARTMENT_NAME'] = $arDepartmentName[$idDepartment];
                        $this->arResult['DEPARTMENT_ID'] = $idDepartment;
                    } else {
                        $this->arResult['STATUS_ERR'] = 'У руководителя в отеделе нет подчиненных';
                    }
                }
                if (empty($this->arResult['STATUS_ERR'])) {
                    //Получаем список пресейлов на текущий момент
                    $obAllUsers = UserGroupTable::getList([
                        'select' => [
                            'ID'        => 'USER_ID',
                            'NAME'      => 'USER.NAME',
                            'LAST_NAME' => 'USER.LAST_NAME',
                            'IS_MAIN_PRESALE' => 'USER.UF_MAIN_PRESALE',
                        ],
                        'order'  => ['USER.NAME' => 'ASC'],
                        'filter' => $arFilterForPresales,
                    ]);

                    $arAllUsers = [];
                    while ($arUser = $obAllUsers->fetch()) {
                        $userFullName = getPresaleUserName($arUser['NAME'], $arUser['LAST_NAME']);
                        if (!empty($userFullName)) {
                            $arAllUsers[$arUser['ID']]['FULL_NAME'] = $userFullName;
                            $arAllUsers[$arUser['ID']]['IS_MAIN_PRESALE'] = $arUser['IS_MAIN_PRESALE'];
                        }
                    }
                    $this->arResult['USER_LIST'] = $arAllUsers;

                    //Получаем список пользователей, доступных для становлениям пресейлами(выводятся в поиске)
                    $obAllUsers = UserTable::getList([
                        'select' => [
                            'ID',
                            'NAME',
                            'SECOND_NAME',
                            'LAST_NAME',
                        ],
                        'order'  => ['LAST_NAME' => 'ASC'],
                        'filter' => $arFilterForSearch,
                    ]);
                    $arAllUsersJsonToSearch = [];
                    while ($arUser = $obAllUsers->fetch()) {
                        $userFullName = getPresaleUserName($arUser['NAME'], $arUser['LAST_NAME']);
                        if (!empty($userFullName)) {
                            $arAllUsersJsonToSearch[] = [
                                'name' => $userFullName,
                                'id'   => $arUser['ID'],
                            ];
                        }
                    }

                    //Т.к данный компоненты обрабатывется только 1 раз при загрузке страницы, остальная вся логика идет на ajax,
                    //В списке пользоветелей доступных для поиска будут доступны и пользователи, которые на текущий момент пресейлы.
                    $this->arResult['USER_LIST_TO_SEARCH_JSON'] = json_encode($arAllUsersJsonToSearch, JSON_UNESCAPED_UNICODE);
                }
            } else {
                //Пользователь не имеет доступ к странице
                $this->arResult['STATUS_ERR'] = 'Пользователь не имеет доступа к данной странице';
            }
        } else {
            //Пользователь не имеет доступ к странице
            $this->arResult['STATUS_ERR'] = 'Пользователь перешел по некорректной ссылке';
        }

        $this->includeComponentTemplate();
    }
}

