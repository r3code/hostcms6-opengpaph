# hostcms6-opengpaph

Генератор мета тегов микроразметки OpenGraph для HostCMS 6 (страницы, группы, товары)

Позволяет вставлять в макет `meta` теги заполненные данными согласно протоколу [OpenGraph](http://ogp.me).
Заполняет свойства `og:*`. Свойство `og:url` формируется согласно пути заданному в настройках информационного элемента, с сохранением регистра независимо от того как пользователь ввел URL в строке - это позволяет избежать создания дублей в поисковых системах, т.к. HostCMS одинаково обрабатывает URL в верхнем регистре и нижнем, т.е. "oLLin" и "OlliN" указывают на одну страницу и при обходе поисковик видит одно и тоже содержание на разных адресах.


## Установка модуля

## Использование в коде

Для правильного разбора OpenGraph тегов некоторым валидаторам требуется явно задать, где читать описание для префиксов.
Мы используем префикс `product` для разметки данных карточек товаров и валидатор микроразметки от Яндекс будет выдавать ошибки, если префикс не указан явно.
Для этиъ случаев добавьте тег `<head>` указание, где читать определение для префикса:

	prefix=
	    "og: http://ogp.me/ns#
	     fb: http://ogp.me/ns/fb#  
	     product: http://ogp.me/ns/product#"

Например:

	<head prefix=
	    "og: http://ogp.me/ns#
	     fb: http://ogp.me/ns/fb#  
	     product: http://ogp.me/ns/product#" >
        ...
        </head>

### Вставка meta-тегов OpenGraph в код макета

Данный модуль содерит один публичный метод `Og_Utils::generateOpenGraphMeta($siteName,  $fallbackOgImageUrl)`, где `$siteName` - название сайта для отображения в `og:site_name`, `$fallbackOgImageUrl` - адрес картинки для использования в `og:image`, если информационный элемент не имеет свойства `image_large`.
Meta-теги должны быть размещены в `<head>`. 

Добавим вызов к модулю для вставки OpenGraph микроразметки страницы:
	
	<head prefix=
		"og: http://ogp.me/ns#
		 fb: http://ogp.me/ns/fb#  
		 product: http://ogp.me/ns/product#">
		<meta charset="utf-8"/>
		
		<title><?php Core_Page::instance()->showTitle(); ?></title>
		<?php 
			$siteName = 'example.org'; 
			$fallbackOgImageUrl = '/img/og-default-image.png'; // поменять на свой
		echo Og_Utils::generateOpenGraphMeta($siteName,  $fallbackOgImageUrl);
		?>

Вызов должен быть только один раз.

В результате с сгенерированном HTML-коде вы увидите meta-теги с разметкой OpenGraph.

*Для структуры сайта*
	
	// вызов в мекете <?php echo Og_Utils::generateOpenGraphMeta("bestkosmetika.ru", "/__bk2018/img/favicon/apple-touch-icon-152x152.png"); ?>

	<meta property="og:site_name" content="bestkosmetika.ru" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="Магазин профессиональной косметики для волос" />
	<meta property="og:url" content="https://bestkosmetika.ru/" />
	<meta property="og:description" content="«БестКосметика» – оптовый интернет-магазин профессиональной косметики с низкими ценами и бесплатной доставкой по Москве" />
	<meta property="og:image" content="/__bk2018/img/favicon/apple-touch-icon-152x152.png" />

*Для группы магазина/информационной системы*

	<meta property="og:site_name" content="bestkosmetika.ru" />
	<meta property="og:type" content="website" />
	<meta property="og:title" content="SALE - 30%" />
	<meta property="og:url" content="https://bestkosmetika.ru/shop/sale/" />
	<meta property="og:description" content="" />
	<meta property="og:image" content="/__bk2018/img/favicon/apple-touch-icon-152x152.png" />


*Для группы страницы товара*

	<meta property="og:site_name" content="bestkosmetika.ru" />
	<meta property="og:type" content="product" />
	<meta property="og:title" content="Распродажа Крем Шампунь Кумыс" />
	<meta property="og:url" content="https://bestkosmetika.ru/shop/sale/sprejj-konditsioner/" />
	<meta property="og:description" content="Нежно и деликатно очищает волосы от загрязнений и излишней жирности, не нарушая липидную защиту." />
	<meta property="og:image" content="https://bestkosmetika.ru/upload/shop_1/8/3/4/item_8347/IMG_20161228_133421_HDR.jpg" />
	<meta property="og:image:width" content="354" />
	<meta property="og:image:height" content="800" />
	<meta property="product:price:amount" content="100.00" />
	<meta property="product:price:currency" content="RUB" />


*Для статьи информационной системы*

	<meta property="og:site_name" content="bestkosmetika.ru" />
	<meta property="og:type" content="article" />
	<meta property="og:title" content="Окрашивание волос Техникой PIKABU «Пикабу»" />
	<meta property="og:url" content="https://bestkosmetika.ru/articles/okrashivanie-volos-tekhnikoj-pikabu/" />
	<meta property="og:description" content="PIKABU позволит выпустить наружу весь креатив и задор, который скрыт от посторонних глаз, с помощью мягких пастельных направлений, напоминающих цвета детских конфет и игру цветов восходящего солнца. " />
	<meta property="og:image" content="https://bestkosmetika.ru/upload/information_system_5/6/8/9/item_689/information_items_689.jpg" />
	<meta property="og:image:width" content="807" />
	<meta property="og:image:height" content="388" />




