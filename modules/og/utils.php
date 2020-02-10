<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Open Graph Metadata генератор
 *
 * @package HostCMS 6\Og\Utils
 * @version 1.0.0
 * @author Dimitriy S. Sinyavskiy (http://r3code.ru)
 * @copyright 2020
 * @compatibility HostCMS 6.1.4+
 */
class Og_Utils
{

	/**
	 * Og_Utils::generateOpenGraphMeta() - возвращает блок html c meta тегами заполненными в разметке OpenGraph (поддерживает Товары, Статьи информационных систем, Страницы структурв, Главная страница)
	 *
	 * @param string [$arg_siteName] имя сайта для отображения в Meta/og:site_name
     * @param string [$arg_fallbackOgImageUrl] URL для картинки Meta/og:image на слуйчай, если для элемента данных не задана картика в image_large  
	 * @return string строка содержащая необходимые meta теги для og:* свойств
	 *
	 * @example:
	 * <?php echo Og_Utils::generateOpenGraphMeta('My site name', '/images/logo.png'); ?>
     * 
     * обязательно для валидности добавить в head префиксы <head prefix=
    "og: http://ogp.me/ns#
     fb: http://ogp.me/ns/fb#  
     product: http://ogp.me/ns/product#">
	 *
	 */
	static public function generateOpenGraphMeta($arg_siteName, $arg_fallbackOgImageUrl)
	{
        $ogProps = array();
        $ogProps['og:site_name'] = $arg_siteName;
        $ogProps['og:type'] = 'website'; // значение по умолчанию, может быть переопределено позже
        $ogProps['og:title'] = Core_Page::instance()->title;
        $ogProps['og:url'] = self::getSiteBaseURL(); // значение по умолчанию, может быть переопределено/дополнено позже
        $ogProps['og:description'] = strip_tags(Core_Str::cutSentences(Core_Page::instance()->description));
        $ogProps['og:image'] = $arg_fallbackOgImageUrl;
                
        $corePage = Core_Page::instance();
        if (is_object($corePage->object))
        {
            self::fillItemOgMeta($ogProps, $arg_fallbackOgImageUrl);
            return self::createMetaTagsText($ogProps);   
        }
        
        $oStructureEntity = $corePage->structure;
        if ($oStructureEntity)
        {
            self::fillSiteStructureOgMeta($ogProps, $arg_fallbackOgImageUrl);
            return self::createMetaTagsText($ogProps);    
        }
		
		// вернем результат для иных случаев
		return self::createMetaTagsText($ogProps) ;
    }
    
    /**
	 * self::createMetaTagsText() - преобразовать массив свойств OpenGraph в строку тегов <meta>
	 *
	 * @param array [$ogProps] массив свойств назнвание/значение, например, og:type/website
	 * @return string строка содержащая необходимые meta теги для og:* свойств
	 *
	 */
    static private function createMetaTagsText($ogProps)
    {
        $lines = '';
        foreach ($ogProps as $ogProperty => $ogContent)
        {
            $lines = $lines . "\t" . '<meta property="' . htmlspecialchars($ogProperty) . '" content="' . htmlspecialchars($ogContent) . '" />' . PHP_EOL;
        }
        return $lines;
    }

    /**
	 * self::fillSiteStructureOgMeta($ogProps) - дполниить переданный массив  og:* свойствами для структуры сайта
	 *
	 * @param array [$ogProps] массив свойств назнвание/значение, например, og:type/website
	 * @return none
	 *
	 */
    static private function fillSiteStructureOgMeta(&$ogProps, $arg_fallbackOgImageUrl) 
    {
        $corePage = Core_Page::instance();
        $oStructureEntity = $corePage->structure;
        $ogProps['og:type'] = 'website';
        $ogProps['og:title'] = $oStructureEntity->Site->name;
        $ogProps['og:description'] = strip_tags(Core_Str::cutSentences($oStructureEntity->seo_description));
        $ogProps['og:url'] = self::getSiteBaseURL() . $oStructureEntity->getPath(); // допишем к базовому URL путь структуры
        $ogProps['og:image'] = $arg_fallbackOgImageUrl;
        
    }

    /**
	 * self::fillItemOgMeta($ogProps) - дполниить переданный массив  og:* свойствами для элемента информационной системы или страницы магазина
	 *
	 * @param array [$ogProps] массив свойств назнвание/значение, например, og:type/website
     * @param string [$arg_fallbackOgImageUrl] URL для картинки Meta/og:image на слуйчай, если для элемента данных не задана картика в image_large
	 * @return none
	 *
	 */
    static private function fillItemOgMeta(&$ogProps, $arg_fallbackOgImageUrl) 
    {
        $corePage = Core_Page::instance();
        $oStructureEntity = $corePage->structure;
        $ogProps['og:url'] = self::getSiteBaseURL() . $oStructureEntity->getPath();         
        $ogProps['og:description'] = strip_tags(Core_Str::cutSentences($oStructureEntity->seo_description));

        $isInformationItem = $corePage->object instanceof Informationsystem_Controller_Show;
        $isShopItem = $corePage->object instanceof Shop_Controller_Show;
    
        if ( $isInformationItem || $isShopItem )
        {           
            $oItem = $corePage->object->item;     
            if ($oItem)
            {
                $oItemEntity = $isInformationItem
                    ? Core_Entity::factory('Informationsystem_Item', $oItem)
                    : Core_Entity::factory('Shop_Item', $oItem);
    
                $ogType = $isInformationItem ? 'article' : 'product';
    
                $ogProps['og:type'] = $ogType;
                $ogProps['og:title'] = $oItemEntity->name;
                $ogProps['og:description'] = strip_tags(Core_Str::cutSentences($oItemEntity->description));

                $siteBaseURL = self::getSiteBaseURL();                
                $ogProps['og:url'] = $siteBaseURL . $oStructureEntity->getPath() . $oItemEntity->getPath();

                if ($oItemEntity->image_large != '')
                {

                    $ogProps['og:image'] = $siteBaseURL . $oItemEntity->getLargeFileHref();
                    $ogProps['og:image:width'] = $oItemEntity->image_large_width;
                    $ogProps['og:image:height'] = $oItemEntity->image_large_height;
                }

                if ($isShopItem)
                {
                    $ogProps['product:price:amount'] = $oItemEntity->price;
                    $ogProps['product:price:currency'] = $oItemEntity->shop_currency->code;
                }
                return;            
            }

            $oGroup = $corePage->object->group;
            if ($oGroup)
            {                
                self::fillGroupOgMeta($ogProps, $arg_fallbackOgImageUrl);                           
                return;            
            }
        }
    }

    /**
	 * self::fillGroupOgMeta($ogProps, $oGroup, $arg_fallbackOgImageUrl) - дполниить переданный массив  og:* свойствами для группы
	 *
	 * @param array [$ogProps] массив свойств назнвание/значение, например, og:type/website
	 * @param object [$arg_fallbackOgImageUrl] url ссылки на картинку для og:image, если для элемента не задана своя картинка
	 * @return none
	 *
	 */
    static private function fillGroupOgMeta(&$ogProps, $arg_fallbackOgImageUrl) 
    {      
        $corePage = Core_Page::instance();    
        $isInformationItem = $corePage->object instanceof Informationsystem_Controller_Show; 
        $oGroup = $corePage->object->group;         
        $oGroupEntity = $isInformationItem
            ? Core_Entity::factory('Informationsystem_Group', $oGroup)
            : Core_Entity::factory('Shop_Group', $oGroup);

        $ogType = $isInformationItem ? 'article'  : 'website';

        $ogProps['og:type'] = $ogType;
        $ogProps['og:title'] = $oGroupEntity->name;
        $ogProps['og:description'] = $oGroupEntity->seo_description;

        $oStructureEntity = $corePage->structure;

        $siteBaseURL = self::getSiteBaseURL();                 
        $ogProps['og:url'] = $siteBaseURL . $oStructureEntity->getPath() . $oGroupEntity->getPath();
        $ogImageUrl = $arg_fallbackOgImageUrl;
        if ($oGroupEntity->image_large != '')
        {
            $ogImageUrl = $siteBaseURL . $oGroupEntity->getLargeFileHref();
        }
        $ogProps['og:image'] = $ogImageUrl;        

        return;
    }

    /**
	 * self::getsiteBaseURL() - получить базовый URL сайта (http://sitename или https://sitename)
	 *
	 * @return string - имя сайта с протоколом или ""
	 *
	 */
    static private function getSiteBaseURL() {
        $protocol = Core::httpsUses() ? 'https://' : 'http://';
        $siteBaseURL = '';
        $oSite = Core_Entity::factory('Site', CURRENT_SITE);
        $oSite_Alias = $oSite->getCurrentAlias();
        if (!$oSite_Alias)
        {
            throw new \Exception('Can not read Site current alias');
        }
        $siteBaseURL = $protocol . $oSite_Alias->name;

        return $siteBaseURL;
    }
}