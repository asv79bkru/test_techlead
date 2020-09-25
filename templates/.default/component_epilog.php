<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();
\CJSCore::Init(['jquery']);
?>
<script>
    if (typeof presaleUserListToSearch == 'undefined') var presaleUserListToSearch = {};
    presaleUserListToSearch = <?= $arResult['USER_LIST_TO_SEARCH_JSON'] ?>;

    if (typeof presaleGroup == 'undefined') var presaleGroup = '';
    presaleGroup = <?= $arParams['USER_GROUP'] ?>;

    if (typeof maxCountInGroup == 'undefined') var maxCountInGroup = '';
    maxCountInGroup = <?= $arParams['MAX_COUNT_PRESALES_IN_GROUP'] ?>;

    if (typeof departmentId == 'undefined') var departmentId = '';
    departmentId = <?= $arResult['DEPARTMENT_ID'] ?>;
</script>

