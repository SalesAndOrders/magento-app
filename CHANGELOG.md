# Change Log

All notable changes to this project will be documented in this file.

### [1.0.30] 2020-09-02

  * Added: CHANGELOG.md file
  * Added: full_url_key to each product in response
      - this is the product url key with modifications of the Magento according to SEO settings instead of default *url_key*;
  * Added: Configurable product change price plugin. 
      - Changes price according to "oid" query parameter.
      - *oid* - contains child product id value of configurable product.
      - Price changes to selected child product id price for non JavaScript pages only. 
  * Added: Child products with data for the configurable products
  * Added: Child products urls for the parent configurable product
      - *configurable_option_url*  - contains an array of built URLs like  in the *parent_url* child produc attribute
  * Added: Each child product contains attribute *parent_url* 
      - *parent_url* - is a combination of *product_url_key* , oid query parameter with *child_product_id* value and hash configurable product attributes options.
      - Formula:
          - *parent_product_url_key*?oid=*child_product_id*#configurable_option_attribute_id=child_product_attribute_value   
      - Example for *child_product_id* 695 : 
          - default/argus-all-weather-tank.html?oid=695#93=5479&152=5593
  * Added: extension attributes:
      - child_product
          - Contains child products for Configurable products type 
      - full_url_key
          - Contains product url_rewrite URL key with SEO language prefix and '.html' postfix
     - configurable_option_url
         - contains an array of built URLs like  in the *parent_url*