<?php
/**
 * @package     WT SEO Meta templates - Virtuemart
 * @version     2.0.0
 * @Author 		Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2023 Sergey Tolkachyov
 * @license     GNU/GPL 3
 * @since 		1.0.0
 */

namespace Joomla\Plugin\System\Wt_seo_meta_templates_virtuemart\Extension;

// No direct access
defined( '_JEXEC' ) or die;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Event\SubscriberInterface;

class Wt_seo_meta_templates_virtuemart extends CMSPlugin implements SubscriberInterface
{
	protected $autoloadLanguage = true;
	protected $allowLegacyListeners = false;

	/**
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 2.0.0
	 *
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onWt_seo_meta_templatesAddVariables' => 'onWt_seo_meta_templatesAddVariables'
		];
	}

	public function onWt_seo_meta_templatesAddVariables($event) : void
	{
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: start');
		$app = $this->getApplication();
		$option = $app->getInput()->get('option');
		if($option == 'com_virtuemart'){

		// Load Virtuemart config and models
			if (!class_exists( 'VmConfig' )) {
				require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
			}

			\VmConfig::loadConfig();
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: After load Virtuemart config');
			$variables = array();
			// Short codes for virtuemart category view
			if($app->getInput()->get('view') == 'category' && $app->getInput()->get('virtuemart_category_id') != 0 ){
				$virtuemart_category_id = $app->getInput()->get('virtuemart_category_id');

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: Before load Virtuemart category');
				$vm_category_model = \VmModel::getModel('category');
				$vm_category = $vm_category_model->getCategory($virtuemart_category_id);
			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: After load Virtuemart category');

				/*
				 * Virtuemart category variables for short codes
				 */
				//Virtuemart category name
				$variables[] = [
						'variable' => 'VM_CATEGORY_NAME',
						'value'    => $vm_category->category_name,
					];
				//Virtuemart category id
				$variables[] = [
						'variable' => 'VM_CATEGORY_ID',
						'value'    => $vm_category->virtuemart_category_id,
					];
				//Virtuemart parent category name
				if($vm_category->category_parent_id != 0){
					$vm_parent_category = $vm_category_model->getCategory($vm_category->category_parent_id);
					$vm_parent_category_name = $vm_parent_category->category_name;
					$variables[] = [
						'variable' => 'VM_PARENT_CATEGORY_NAME',
						'value'    => $vm_parent_category_name,
					];
				}

				//Массив для тайтлов и дескрипшнов по формуле для передачи в основной плагин
				$seo_meta_template = array();

				/*
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if($this->params->get('show_debug') == 1)
				{
					$this->prepareDebugInfo('','<p><strong>Virtuemart Title</strong>: '.$vm_category->customtitle.'</p>');
					$this->prepareDebugInfo('','<p><strong>Virtuemart Meta desc:</strong> '.$vm_category->metadesc.'</p>');
				}

				if($this->params->get('global_vm_category_title_replace') == 1){

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $vm_category->customtitle
					 */

					if($this->params->get('global_vm_category_title_replace_only_empty') == 1){
						if($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_TITLE_REPLACE_ONLY_EMPTY').'</p>');
						}
						if(empty($vm_category->customtitle) == true){
							if($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_TITLE_FOUND').'</p>');
							}
							$title_template = $this->params->get('virtuemart_category_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}else{
					//Переписываем все глобально
						if($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_TITLE_REPLACE').'</p>');
						}
						$title_template = $this->params->get('virtuemart_category_title_template');
						$seo_meta_template['title'] = $title_template;
					}
				}

				/*
				 * Если включена глобальная перезапись description категории. Все по формуле.
				 */

				if($this->params->get('global_vm_category_description_replace') == 1){

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $vm_category->metadesc
					 */

					if($this->params->get('global_vm_category_description_replace_only_empty') == 1){
						if($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_META_DESCRIPTION_REPLACE_ONLY_EMPTY').'</p>');
						}

						if(empty($vm_category->metadesc) == true){
							if($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_META_DESCRIPTION_FOUND').'</p>');
							}
							$description_template = $this->params->get('virtuemart_category_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}else{
						//Переписываем все глобально
						if($this->params->get('show_debug') == 1)
						{
							$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_META_DESCRIPTION_REPLACE').'</p>');
						}
						$description_template = $this->params->get('virtuemart_category_meta_description_template');
						$seo_meta_template['description'] = $description_template;
					}
				}
			}
			// Short codes for virtuemart product details view
			elseif ($app->getInput()->get('view') == 'productdetails'){
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: Before load Virtuemart product');
				$product_model = \VmModel::getModel('product');
				$virtuemart_product_id = $app->getInput()->get('virtuemart_product_id');

				/*
				 * $virtuemart_product_id = NULL,
				 * $front = TRUE,
				 * $withCalc = TRUE,
				 * $onlyPublished = TRUE,
				 * $quantity = 1,
				 * $virtuemart_shoppergroup_ids = 0
				 */

				$vm_product = $product_model->getProduct($virtuemart_product_id);
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: After load Virtuemart product');
				/*
				 * Virtuemart category variables for short codes
				 */
				//Virtuemart product id
				$variables[] = [
					'variable' => 'VM_PRODUCT_ID',
					'value'    => $vm_product->virtuemart_product_id,
				];
				//Virtuemart product SKU
				$variables[] = [
					'variable' => 'VM_PRODUCT_SKU',
					'value'    => $vm_product->product_sku,
				];

				//Virtuemart product GTIN
				$variables[] = [
					'variable' => 'VM_PRODUCT_GTIN',
					'value'    => $vm_product->product_gtin,
				];

				//Virtuemart product MPN
				$variables[] = [
					'variable' => 'VM_PRODUCT_MPN',
					'value'    => $vm_product->product_mpn,
				];

				//Virtuemart product MPN
				$variables[] = [
					'variable' => 'VM_PRODUCT_NAME',
					'value'    => $vm_product->product_name,
				];

				//Virtuemart product weight
				$variables[] = [
					'variable' => 'VM_PRODUCT_WEIGHT',
					'value'    => $vm_product->product_weight,
				];

				//Virtuemart product length
				$variables[] = [
					'variable' => 'VM_PRODUCT_LENGTH',
					'value'    => $vm_product->product_length,
				];

				//Virtuemart product width
				$variables[] = [
					'variable' => 'VM_PRODUCT_WIDTH',
					'value'    => $vm_product->product_width,
				];

				//Virtuemart product height
				$variables[] = [
					'variable' => 'VM_PRODUCT_HEIGHT',
					'value'    => $vm_product->product_height,
				];


				//Virtuemart product in stock
				$variables[] = [
					'variable' => 'VM_PRODUCT_IN_STOCK',
					'value'    => $vm_product->product_in_stock,
				];

				//Virtuemart product ordered how many
				$variables[] = [
					'variable' => 'VM_PRODUCT_ORDERED',
					'value'    => $vm_product->product_ordered,
				];

				//Virtuemart product package weight
				$variables[] = [
					'variable' => 'VM_PRODUCT_PACKAGE_WEIGHT',
					'value'    => $vm_product->product_packaging,
				];

				//Virtuemart product's category name
				$variables[] = [
					'variable' => 'VM_PRODUCT_CATEGORY_NAME',
					'value'    => $vm_product->category_name,
				];

				//Virtuemart product's canonical category name
				$variables[] = [
					'variable' => 'VM_PRODUCT_CANON_CATEGORY_NAME',
					'value'    => $vm_product->canonCatIdname,
				];

				$currency = \CurrencyDisplay::getInstance();

				//If we have a product's zero base price we can replace digits with text
				if((int)$vm_product->prices['basePrice'] == 0 && $this->params->get('replace_zero_price_with_text',0) == 1 && !empty($this->params->get('zero_price_replace_text',''))){
					$vm_product_base_price = $this->params->get('zero_price_replace_text','');
				} else {
					$vm_product_base_price = $currency->priceDisplay($vm_product->prices['basePrice'],'',1,true);
				}

				//Virtuemart product base price
				$variables[] = [
					'variable' => 'VM_PRODUCT_BASE_PRICE',
					'value'    => $vm_product_base_price,
				];

				//If we have a product's zero sales price we can replace digits with text
				if(empty($vm_product->prices['salesPrice']) == true && $this->params->get('replace_zero_price_with_text',0) == 1 && !empty($this->params->get('zero_price_replace_text',''))){
					$vm_product_sales_price = $this->params->get('zero_price_replace_text','');
				} else {
					$vm_product_sales_price = $currency->priceDisplay($vm_product->prices['salesPrice'],'',1,true);;
				}

				//Virtuemart product sales price
				$variables[] = [
					'variable' => 'VM_PRODUCT_SALES_PRICE',
					'value'    => $vm_product_sales_price,
				];

				//Virtuemart product unit price
				$variables[] = [
					'variable' => 'VM_PRODUCT_UNIT_PRICE',
					'value'    => $vm_product->prices['unitPrice'],
				];

			//@todo Get custom fields data $vm_product->customfieldsSorted


				if($this->params->get('global_vm_product_title_replace') == 1){

						/*
						 * Если переписываем только пустые. Там, где пустое
						 * $vm_category->customtitle
						 */

						if($this->params->get('global_vm_product_title_replace_only_empty') == 1){
							if($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_PRODUCT_TITLE_REPLACE_ONLY_EMPTY').'</p>');
							}
							if(empty($vm_product->customtitle) == true){
								if($this->params->get('show_debug') == 1)
								{
									$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_PRODUCT_TITLE_FOUND').'</p>');
								}
								$title_template = $this->params->get('virtuemart_product_title_template');
								$seo_meta_template['title'] = $title_template;
							}
						}else{
							//Переписываем все глобально
							if($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_VIRTUEMART_DEBUG_GLOBAL_VM_PRODUCT_TITLE_REPLACE').'</p>');
							}
							$title_template = $this->params->get('virtuemart_product_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}

					/*
					 * Если включена глобальная перезапись description категории. Все по формуле.
					 */

					if($this->params->get('global_vm_product_meta_description_replace') == 1){

						/*
						 * Если переписываем только пустые. Там, где пустое
						 * $vm_category->metadesc
						 */

						if($this->params->get('global_vm_product_meta_description_replace_only_empty') == 1){
							if($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_PRODUCT_META_DESCRIPTION_REPLACE_ONLY_EMPTY').'</p>');
							}

							if(empty($vm_product->metadesc) == true){
								if($this->params->get('show_debug') == 1)
								{
									$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_PRODUCT_META_DESCRIPTION_FOUND').'</p>');
								}
								$description_template = $this->params->get('virtuemart_product_meta_description_template');
								$seo_meta_template['description'] = $description_template;
							}
						}else{
							//Переписываем все глобально
							if($this->params->get('show_debug') == 1)
							{
								$this->prepareDebugInfo('','<p>'.Text::_('PLG_WT_SEO_META_TEMPLATES_VIRTUEMART_DEBUG_GLOBAL_VM_PRODUCT_META_DESCRIPTION_REPLACE').'</p>');
							}
							$description_template = $this->params->get('virtuemart_product_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}


			}

			$data = array(
				'variables' => $variables,
				'seo_tags_templates' => $seo_meta_template,
			);


			if($this->params->get('show_debug') == 1)
			{
				$this->prepareDebugInfo('Data array sends to WT SEO Meta templates plugin',$data);
			}
			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: Before return data. End.');

			$event->setArgument('result',$data);
		}
	}

	/**
	 * Prepare html output for debug info from main function
	 *
	 * @param $debug_section_header string
	 * @param $debug_data           string|array
	 *
	 * @return void
	 * @since 2.0.0
	 */
	private function prepareDebugInfo($debug_section_header, $debug_data): void
	{
		if ($this->params->get('show_debug') == 1)
		{
			$session      = $this->getApplication()->getSession();
			$debug_output = $session->get("wtseometatemplatesdebugoutput");
			if (!empty($debug_section_header))
			{
				$debug_output .= "<details style='border:1px solid #0FA2E6; margin-bottom:5px;'>";
				$debug_output .= "<summary style='background-color:#384148; color:#fff; padding:10px;'>" . $debug_section_header . "</summary>";
			}

			if (is_array($debug_data) || is_object($debug_data))
			{
				$debug_data   = print_r($debug_data, true);
				$debug_output .= "<pre style='background-color: #eee; padding:10px;'>";
			}

			$debug_output .= $debug_data;
			if (is_array($debug_data) || is_object($debug_data))
			{
				$debug_output .= "</pre>";
			}
			if (!empty($debug_section_header))
			{
				$debug_output .= "</details>";
			}
			$session->set("wtseometatemplatesdebugoutput", $debug_output);
		}
	}
}