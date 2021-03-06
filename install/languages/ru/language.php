<?php
/*
*---------------------------------------------------------
*
*	CartET - Open Source Shopping Cart Software
*	http://www.cartet.org
*
*---------------------------------------------------------
*/

$language = array(
	'step_1' => 'Приветствие',
	'step_2' => 'Лицензия',
	'step_3' => 'Права доступа',
	'step_4' => 'Проверка PHP',
	'step_5' => 'База данных',
	'step_6' => 'Администратор',
	'step_7' => 'Настройки',
	'step_8' => 'Завершение установки',

	'main_title' => 'Установка CartET',
	'main_copyright' => '&copy; '.date("Y").' <a target="_blank" href="http://osc-cms.com">CartET</a> | <a target="_blank" href="http://osc-cms.com/forum">Support</a>',

	'yes' => 'Да',
	'no' => 'Нет',
	'installed' => 'Установлено',
	'not_installed' => 'Не найдено',
	'next' => 'Продолжить',
	'update' => 'Обновить',
	'step_error' => '<span class="negative">Вы не сможете продолжить установку до тех пор, пока условия отмеченные красным не будут исправлены.</span><br />Обратитесь в службу поддержки вашего хостинга с просьбой обеспечить необходимые условия',

	'admin_1' => 'Email',
	'admin_2' => 'Пароль',
	'admin_3' => 'Вы делали обновление.<br />Создание администратора не требуется.',
	'admin_4' => 'Заполните поля Email и Пароль',
	'admin_default_firstname' => 'John',
	'admin_default_lastname' => 'Smith',
	'admin_default_street_address' => 'ул. Мира 346, кв. 78',
	'admin_default_postcode' => '123456',
	'admin_default_city' => 'Москва',
	'admin_default_state' => 'Москва',
	'admin_default_telephone' => '123456789',
	'admin_default_store_name' => 'Название магазина',
	'admin_default_company' => 'Название компании',

	'config_1' => 'Запись данных в файлы:',
	'config_2' => 'Указанные файлы должны быть доступны для записи.',
	'config_3' => 'После записи необходимо будет сделать эти файлы недоступными для записи.',
	'config_4' => 'Файл config.php недоступен для записи',
	'config_5' => 'Файл htaccess.txt недоступен для записи',
	'config_6' => 'Вы делали обновление.<br />Изменение файлов не требуется.',

	'db_1' => 'Укажите данные для подключения к базе MySQL<br />База данных должна быть в кодировке <b>utf8_general_ci</b>',
	'db_2' => 'Сервер MySQL',
	'db_3' => 'Пользователь',
	'db_4' => 'Пароль',
	'db_5' => 'База данных',
	'db_6' => 'Префикс таблиц',
	'db_7' => 'Хранить сессии в файлах',
	'db_8' => 'Хранить сессии в базе данных',
	'db_9' => 'Установить тестовую базу товаров',
	'db_10' => 'Обновление не затронет уже имеющиеся данные.<br />Перед обновление рекомендуется сделать резервную копию базы!',
	'db_11' => 'Ошибка импорта базы данных\nПроверьте правильность реквизитов',
	'db_update_1' => 'Обновление с 1.0.0 до 1.0.1',
	'db_update_2' => 'Обновление с 1.0.1 до 1.1.0',

	'finish_1' => 'Установка CartET завершена.',
	'finish_2' => 'Перед тем как продолжить, удалите папку <b>install</b> в корне сайта.',
	'finish_3' => '<a class="button button-rounded button-flat-action" href="../">Перейти на главную страницу сайта</a>',
	'finish_4' => '<a href="../login_admin.php">Перейти в панель управления</a>',

	'license_1' => 'CartET распространяется по лицензии <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU/GPL</a> версии 2.',
	'license_2' => 'Я согласен с условиями лицензии',
	'license_3' => 'Вы должны согласиться с условиями лицензии',

	'dir_1' => 'Необходимо выставить права на запись (0777)',

	'php_1' => 'Версия интерпретатора (Требуется PHP 5.2 или выше)',
	'php_2' => 'Установленная версия',
	'php_3' => 'Требуемые расширения',
	'php_4' => 'Данные расширения необходимы для работы CartET',
	'php_5' => 'Рекомендуемые расширения',
	'php_6' => 'Данные расширения не являются необходимыми, но без них будет недоступна часть функционала',

	'start_1' => 'Данная версия CartET является RC-версией и предназначена только для тестирования.',
	'start_2' => 'Мастер установки CartET проверит удовлетворяет ли ваш сервер системным требованиям.',
	'start_3' => 'В процессе работы мастер задаст несколько вопросов, необходимых для корректной установки и настройки CartET.',
	'start_4' => 'Перед началом установки необходимо создать чистую базу данных MySQL в кодировке <b>utf8_general_ci</b>',
	'start_5' => 'Выберите тип действий',
	'start_6' => 'Установка',
	'start_7' => 'Обновление',
	'start_8' => 'Вы должны выбрать тип действия',
);

return $language;