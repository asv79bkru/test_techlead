<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED != true) die();

use \Bitrix\Main\UserGroupTable, \Bitrix\Main\UserTable;

$this->addExternalCss(SITE_TEMPLATE_PATH . '/css/employee.css');

?>
<?
if ($arResult['STATUS_ERR']) { ?>
    <div id='error-info'><?= $arResult['STATUS_ERR'] ?></div>
<? } else { ?>
    <div class="employee-filter-block">
        <? if ($arParams['LIST_URL']) { ?>
            <span class="user-presale-list-span">
                <a href="<?= $arParams['LIST_URL']; ?>" class="user-presale-list">
                    К списку отвественных за оценку
                </a>
             </span>
        <? } ?>
        <div class="employee-filter-left"></div>
        <div class="employee-filter-right"></div>
    </div>
    <? if (!empty($arResult['DEPARTMENT_NAME'])) { ?>
        <h3>Направление <?= $arResult['DEPARTMENT_NAME']; ?></h3>
    <? } ?>
    <div id='error-info'></div>
    <div class="bx24-top-bar-search-wrap employee-search-wrap">
        <form>
            <input type="text" name="referal" placeholder="" value="" class="who" autocomplete="off">
            <ul class="search_result"></ul>
            <span class="bx24-top-bar-search-icon"></span>
        </form>
    </div>
    <span id="user-block-presales" class="task-dashed-link task-dashed-link-add tasks-additional-block-link">
	<?
    foreach ($arResult['USER_LIST'] as $id => $user) { ?>
        <span class="task-dashed-link-inner">
            <?= $user['FULL_NAME']; ?>
            <a href="javascript:void(0)" data-id="<?= $id ?>" class="delete-user">
                &times;
            </a>
            <a href="javascript:void(0)" data-id="<?= $id ?>" class="<?= $user['IS_MAIN_PRESALE'] ? 'delete-main-presale' : 'make-main-presale';?>">
                <?= $user['IS_MAIN_PRESALE'] ? 'Убрать статус "Главный пресейл"' : 'Поставить статус "Главный пресейл"';?>
            </a>
        </span>
        <?
    }
    ?>
    </span>
<?php } ?>

