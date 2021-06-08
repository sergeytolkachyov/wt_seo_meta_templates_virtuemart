<?php
/**
 * @package     WT JoomShopping B24 PRO
 * @version     2.5.0
 * @Author Sergey Tolkachyov, https://web-tolk.ru
 * @copyright   Copyright (C) 2020 Sergey Tolkachyov
 * @license     GNU/GPL http://www.gnu.org/licenses/gpl-2.0.html
 * @since       1.0
 */
// No direct access
defined( '_JEXEC' ) or die;
use Joomla\CMS\Application\CMSApplication as App;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Profiler\Profiler;
class plgSystemWt_seo_meta_templates_virtuemart extends CMSPlugin
{
	public function __construct( &$subject, $config )
	{

		parent::__construct( $subject, $config );
		$this->loadLanguage();
	}

	public function onWt_seo_meta_templatesAddVariables(){
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: start');
		$app = Factory::getApplication();
		$option = $app->input->get('option');
		if($option == 'com_virtuemart'){

		// Load Virtuemart config and models
			if (!class_exists( 'VmConfig' )) {
				require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
			}

			VmConfig::loadConfig();
		!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: After load Virtuemart config');
			$variables = array();
			// Short codes for virtuemart category view
			if($app->input->get('view') == 'category'){
				$virtuemart_category_id = $app->input->get('virtuemart_category_id');

			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: Before load Virtuemart category');
				$vm_category_model = VmModel::getModel('category');
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

				//Количество товаров в категории и всех дочерних
//				$total_items_count = $this->countProductsByCategoryRecursive($vm_category->virtuemart_vendor_id,$vm_category->virtuemart_category_id);
//				$variables[] = [
//					'variable' => 'VM_CATEGORY_TOTAL_PRDUCTS_COUNT',
//					'value'    => $total_items_count,
//				];

				//Массив для тайтлов и дескрипшнов по формуле для передачи в основной плагин
				$seo_meta_template = array();

				/*
				 * Если включена глобальная перезапись <title> категории. Все по формуле.
				 */
				if($this->params->get('show_debug') == 1){
					echo '<h4>WT SEO Meta templates - Virtuemart provider plugin debug</h4>';
					echo '<p><strong>Virtuemart Title</strong>: '.$vm_category->customtitle.'</p>';
					echo '<p><strong>Virtuemart Meta desc:</strong> '.$vm_category->metadesc.'</p>';
				}

				if($this->params->get('global_vm_category_title_replace') == 1){

					/*
					 * Если переписываем только пустые. Там, где пустое
					 * $vm_category->customtitle
					 */

					if($this->params->get('global_vm_category_title_replace_only_empty') == 1){
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_TITLE_REPLACE_ONLY_EMPTY');
						}
						if(empty($vm_category->customtitle) == true){
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_TITLE_FOUND');
							}
							$title_template = $this->params->get('virtuemart_category_title_template');
							$seo_meta_template['title'] = $title_template;
						}
					}else{
					//Переписываем все глобально
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_TITLE_REPLACE');
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
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_META_DESCRIPTION_REPLACE_ONLY_EMPTY');
						}

						if(empty($vm_category->metadesc) == true){
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_META_DESCRIPTION_FOUND');
							}
							$description_template = $this->params->get('virtuemart_category_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}else{
						//Переписываем все глобально
						if($this->params->get('show_debug') == 1)
						{
							echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_CATEGORY_META_DESCRIPTION_REPLACE');
						}
						$description_template = $this->params->get('virtuemart_category_meta_description_template');
						$seo_meta_template['description'] = $description_template;
					}
				}
			}
			// Short codes for virtuemart product details view
			elseif ($app->input->get('view') == 'productdetails'){
				!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: Before load Virtuemart product');
				$product_model = VmModel::getModel('product');
				$virtuemart_product_id = $app->input->get('virtuemart_product_id');

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

				//Virtuemart product base price
				$variables[] = [
					'variable' => 'VM_PRODUCT_BASE_PRICE',
					'value'    => $vm_product->prices['basePrice'],
				];

				//Virtuemart product sales price
				$variables[] = [
					'variable' => 'VM_PRODUCT_SALES_PRICE',
					'value'    => $vm_product->prices['salesPrice'],
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
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_PRODUCT_TITLE_REPLACE_ONLY_EMPTY');
							}
							if(empty($vm_product->customtitle) == true){
								if($this->params->get('show_debug') == 1)
								{
									echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_PRODUCT_TITLE_FOUND');
								}
								$title_template = $this->params->get('virtuemart_product_title_template');
								$seo_meta_template['title'] = $title_template;
							}
						}else{
							//Переписываем все глобально
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_PRODUCT_TITLE_REPLACE');
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
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_PRODUCT_META_DESCRIPTION_REPLACE_ONLY_EMPTY');
							}

							if(empty($vm_product->metadesc) == true){
								if($this->params->get('show_debug') == 1)
								{
									echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_EMPTY_PRODUCT_META_DESCRIPTION_FOUND');
								}
								$description_template = $this->params->get('virtuemart_category_meta_description_template');
								$seo_meta_template['description'] = $description_template;
							}
						}else{
							//Переписываем все глобально
							if($this->params->get('show_debug') == 1)
							{
								echo Text::_('PLG_WT_SEO_META_TEMPLATES_DEBUG_GLOBAL_VM_PRODUCT_META_DESCRIPTION_REPLACE');
							}
							$description_template = $this->params->get('virtuemart_product_meta_description_template');
							$seo_meta_template['description'] = $description_template;
						}
					}


			}//elseif ($app->input->get('view') == 'productdetails'){





			$data = array(
				'variables' => $variables,
				'seo_tags_templates' => $seo_meta_template,
			);


			if($this->params->get('show_debug') == 1)
			{
				echo '<h4>$data array sends to WT SEO Meta templates plugin</h4><pre>';
				print_r($data);
				echo '</pre>';
			}
			!JDEBUG ?: Profiler::getInstance('Application')->mark('<strong>plg WT SEO Meta templates - Virtuemart provider plugin</strong>: Before return data. End.');
			return $data;
		}//if($option == 'com_virtuemart')
	}



//	function countProductsByCategoryRecursive ($vm_vendor_id, $categoryId = 0) {
//
//		/*
//		 * $vendorId,
//		 * $virtuemart_category_id = 0,
//		 * $childId = false,
//		 * $onlyPublished = true,
//		 * $media = true,
//		 * $keyword = '',
//		 * $selectedOrdering = null,
//		 * $orderDir = null,
//		 * $limitStart = 0,
//		 * $limit = 0
//		 */
//		$childrenCategories = $this->vm_category_model->getChildCategoryListObjectByCachedOption($vm_vendor_id, $categoryId,false,true,false,'',null,null,0,0);
//		$product_count = 0;
//		if(!$childrenCategories){
//			$product_count += $this->vm_category_model->countProducts ($categoryId);
//		}else{
//			foreach($childrenCategories as $value){
//				$product_count += $this->countProductsByCategoryRecursive($value->virtuemart_vendor_id, $value->virtuemart_category_id,false,true,false,'',null,null,0,0);
//				echo $product_count;
//			}
//		}
//
//		return $product_count;
//	}




}//plgSystemWt_seo_meta_templates_virtuemart