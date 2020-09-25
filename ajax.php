<?
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application,
    Bitrix\Main\UserTable,
    Bitrix\Main\UserGroupTable;

$context = Application::getInstance()->getContext();
$request = $context->getRequest();
$action = $request['action'];
//Событие удаление пользователя
if ($action == 'DEL_USER') {
    $userId = $request['id'];
    $userGroup = $request['group'];
    if (!empty($userId) && !empty($userGroup)) {
        $result = UserGroupTable::delete(['GROUP_ID' => $userGroup, 'USER_ID' => $userId]);
        if (!$result->isSuccess()) {
            echo json_encode(['success' => 'false']);
        } else {
            echo json_encode(['success' => 'true']);
        }
    } else {
        echo json_encode(['success' => 'false']);
    }
} else if ($action == 'ADD_USER') {

    $userId = $request['id'];
    $userGroup = $request['group'];
    $maxCountInGroup = $request['maxCount'];
    $requestDepartmentId = $request['departmentId'];

    if (!empty($userId) && !empty($userGroup) && !empty($maxCountInGroup) && !empty($departmentId)) {
        $res = UserGroupTable::getList(['filter' => ['USER_ID' => $userId, 'GROUP_ID' => $userGroup]]);
        if ($row = $res->fetch()) {
            echo json_encode(['success' => 'false', 'text_error' => 'Пользователь уже находится в данной группе']);
        } else {
            //Получаем отделы, в которых работает сотрудник
            $userDepartment = [];
            $obAllUsers = UserTable::getList([
                'select' => [
                    'ID',
                    'NAME',
                    'LAST_NAME',
                    'UF_DEPARTMENT',
                ],
                'order'  => ['LAST_NAME' => 'ASC'],
                'filter' => [
                    'ID' => $userId,
                ],
            ]);

            if ($arUser = $obAllUsers->fetch()) {
                $userDepartment = $arUser['UF_DEPARTMENT'];
                $userFullName = getPresaleUserName($arUser['NAME'], $arUser['LAST_NAME']);
            }

            $structure = CIntranetUtils::getStructure();
            $arDepartments = $structure['DATA'];
            //Получаем массив руководителей подразделений
            if (!empty($arDepartments)) {
                $arEmployeesDepartment = array_column($arDepartments, 'EMPLOYEES', 'ID');
            }

            //Флаг возможности добавить пользователя в пресейлы
            $canAddUser = true;

            //Пробегаемся по каждому, и смотрим чтобы в отделе было меньше чем 2 человека пресейла
            $arFilterForPresales = [
                'GROUP_ID'    => $userGroup,
                'USER.ACTIVE' => 'Y',
                '=USER_ID'    => $arEmployeesDepartment[$requestDepartmentId],
            ];

            $obAllUsers = UserGroupTable::getList([
                'select' => [
                    'ID' => 'USER_ID',
                ],
                'order'  => ['USER.NAME' => 'ASC'],
                'filter' => $arFilterForPresales,
            ]);
            $arAllUsers = [];
            while ($arUser = $obAllUsers->fetch()) {
                $arAllUsers[$arUser['ID']] = $arUser['ID'];
            }
            if (count($arAllUsers) >= $maxCountInGroup) {
                $canAddUser = false;
            }

            if ($canAddUser) {

                UserGroupTable::add([
                    'USER_ID'  => $userId,
                    'GROUP_ID' => $userGroup,
                ]);

                if (empty($userDepartment)) {
                    $arFields = ['UF_DEPARTMENT' => [$requestDepartmentId]];
                    $user = new CUser;
                    $user->Update($userId, $arFields);
                }

                $resultText = '<span class="task-dashed-link-inner">' . $userFullName . '<a href="javascript:void(0)" data-id="' . $userId . '" class="delete-user">&times;</a></span>';
                echo json_encode(['success' => 'true', 'html_text' => $resultText]);
            } else {
                echo json_encode(['success' => 'false', 'text_error' => 'Максимальное количество ответственных за оценки в направлении - ' . $maxCountInGroup]);
            }
        }
    } else {
        echo json_encode(['success' => 'false', 'text_error' => 'Не корректные входные данные']);
    }
} else if ($action == 'LIVE_SEARCH') {
    $referal = strtoupper(trim(strip_tags(stripcslashes(htmlspecialchars($request['referal'])))));
    if (!empty($request['userList'])) {
        $arUser = $request['userList'];
        $list = '';
        foreach ($arUser as $userElem) {
            if (strpos(strtoupper($userElem['name']), $referal) !== false) {
                $list .= "\n<li data-id='" . $userElem['id'] . "'>" . $userElem['name'] . "</li>";
            }
        }
        if (empty($list)) {
            echo json_encode(['success' => 'false', 'list' => '']);
        } else {
            echo json_encode(['success' => 'true', 'list' => $list]);
        }
    } else {
        echo json_encode(['success' => 'false', 'list' => '']);
    }
} else if ($action == 'MAKE_MAIN_PRESALE') {
    $userId = $request['id'];
    $userGroup = $request['group'];
    $departmentId = $request['departmentId'];

    if (!empty($userId) && !empty($userGroup) && !empty($departmentId)) {

        $arFilter = [
            [
                "UF_MAIN_PRESALE"                          => true,
                "UF_DEPARTMENT"                            => [$departmentId],
                "Bitrix\Main\UserGroupTable:USER.GROUP_ID" => $userGroup,

            ],
        ];

        $objUsers = Bitrix\Main\UserTable::getList([
            "select" => ["ID"],
            "filter" => $arFilter,
        ]);
        $arResUsers = $objUsers->fetchAll();

        if (empty($arResUsers)) {
            $fields = ['UF_MAIN_PRESALE' => true];
            $ob = new CUserTypeManager;
            $result = $ob->Update("USER", $userId, $fields);
            if ($result) {
                echo json_encode(['success' => 'true']);
            } else {
                echo json_encode(['success' => 'false']);
            }
        } else {
            echo json_encode(['success' => 'false', 'text_error' => 'В данном подразделении уже есть главный пресейл']);
        }
    } else {
        echo json_encode(['success' => 'false']);
    }
} else if ($action == 'DELETE_MAIN_PRESALE') {
    $userId = $request['id'];
    $userGroup = $request['group'];

    if (!empty($userId) && !empty($userGroup)) {

        $fields = ['UF_MAIN_PRESALE' => false];
        $ob = new CUserTypeManager;
        $result = $ob->Update("USER", $userId, $fields);

        if ($result) {
            echo json_encode(['success' => 'true']);
        } else {
            echo json_encode(['success' => 'false']);
        }

    } else {
        echo json_encode(['success' => 'false']);
    }
} else {
    echo json_encode(['success' => 'false']);
}

