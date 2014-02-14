<?php

function ytp_theme_links__locale_block(&$variables) {
  global $language;
  $items = array();
  // var_dump($variables);
  foreach($variables['links'] as $lang => $info) {
      $name = $info['language']->native;
      $href = isset($info['href']) ? $info['href'] : '';
      $li_classes = array('list-item-class');
      if($lang === $language->language){
            // $li_classes[] = 'active';
      }
      $options = array('attributes' => array(), 'language' => $info['language'], 'html' => true);
      if (!$href) {
          $options['attributes'] = array('class' => array('locale-untranslated'));
      }
      $link = l($name, $href, $options);
      $items[] = array('data' => $link, 'class' => $li_classes);
  }
  $attributes = array('class' => array('nav', 'navbar-nav', 'lang-select'));
  $output = theme_item_list(array('items' => $items,
                                  'title' => '',
                                  'type'  => 'ul',
                                  'attributes' => $attributes
                                  ));
  return $output;
}


/**
 * Implements hook_preprocess_page().
 *
 * @see page.tpl.php
 */
function ytp_theme_preprocess_page(&$variables) {
  // Add information about the number of sidebars.
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['content_column_class'] = ' class="col-sm-4"';
  }
  elseif (!empty($variables['page']['sidebar_first']) || !empty($variables['page']['sidebar_second'])) {
    $variables['content_column_class'] = ' class="col-sm-8"';
  }
  else {
    $variables['content_column_class'] = ' class="col-sm-12"';
  }



  // Primary nav.
  $variables['primary_nav'] = FALSE;
  if ($variables['main_menu']) {
    // Build links.
    $variables['primary_nav'] = menu_tree(variable_get('menu_main_links_source', 'main-menu'));
    // Provide default theme wrapper function.
    $variables['primary_nav']['#theme_wrappers'] = array('menu_tree__primary');
  }

  // Secondary nav.
  $variables['secondary_nav'] = FALSE;
  if ($variables['secondary_menu']) {
    // Build links.
    $variables['secondary_nav'] = menu_tree(variable_get('menu_secondary_links_source', 'user-menu'));
    // Provide default theme wrapper function.
    $variables['secondary_nav']['#theme_wrappers'] = array('menu_tree__secondary');
  }

  $variables['navbar_classes_array'] = array('navbar');

  if (theme_get_setting('bootstrap_navbar_position') !== '') {
    $variables['navbar_classes_array'][] = 'navbar-' . theme_get_setting('bootstrap_navbar_position');
  }
  else {
    $variables['navbar_classes_array'][] = 'container';
  }
  if (theme_get_setting('bootstrap_navbar_inverse')) {
    $variables['navbar_classes_array'][] = 'navbar-inverse';
  }
  else {
    $variables['navbar_classes_array'][] = 'navbar-default';
  }

  $site_section = menu_get_active_trail();
  if ( array_key_exists(1, $site_section) ){
    $variables['site_section'] = $site_section[1]['title'];
  }
  else{
    $variables['site_section'] = '';
  }
 
}

/**
 * Implements hook_process_page().
 *
 * @see page.tpl.php
 */
function ytp_theme_process_page(&$variables) {
  $variables['navbar_classes'] = implode(' ', $variables['navbar_classes_array']);
}

/**
 * Override or insert variables into the html template.
 */
function ytp_theme_preprocess_html(&$variables) {
   switch (theme_get_setting('bootstrap_navbar_position')) {
       case 'fixed-top':
         $variables['classes_array'][] = 'navbar-is-fixed-top';
         break;

       case 'fixed-bottom':
         $variables['classes_array'][] = 'navbar-is-fixed-bottom';
         break;

       case 'static-top':
         $variables['classes_array'][] = 'navbar-is-static-top';
         break;
   }
   $domain = "avoindata.fi";
   if (!empty($_SERVER['HTTP_HOST']) && !is_numeric($_SERVER['HTTP_HOST'][0])) {
    $domain = implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2));
   }

    $title = drupal_get_title();
    if ( $title == '' ){
        $variables['head_title'] = $domain;
    }
    else{
        $variables['head_title'] = implode(' - ', array(drupal_get_title(), $domain));
    }

}

/**
 * Overrides theme_menu_link().
 * This fixes hierarchical vertical block menus when using Bootstrap theme.
 */
function ytp_theme_menu_link(&$variables) {
  $element = $variables['element'];
  $sub_menu = '';

  if (isset($element['#bid']) && ($element['#bid']['module'] == 'menu_block')) {
      $element['#attributes']['class'][] = 'ytp-menulink';
  } 

  if ($element['#below']) {

    // Prevent dropdown functions from being added to management menu so it does not affect the navbar module.
    if (($element['#original_link']['menu_name'] == 'management' && module_exists('navbar'))
        || ((!empty($element['#original_link']['depth']))
        && (isset($element['#bid']) && $element['#bid']['module'] == 'menu_block'))) {
            $sub_menu = drupal_render($element['#below']);
    }
    elseif ((!empty($element['#original_link']['depth'])) && ($element['#original_link']['depth'] == 1)) {
      // Add our own wrapper.
      unset($element['#below']['#theme_wrappers']);
      $sub_menu = '<ul class="dropdown-menu">' . drupal_render($element['#below']) . '</ul>';
      // Generate as standard dropdown.
      $element['#title'] .= ' <span class="caret"></span>';
      $element['#attributes']['class'][] = 'dropdown';
      $element['#localized_options']['html'] = TRUE;

      // Set dropdown trigger element to # to prevent inadvertant page loading when a submenu link is clicked.
      $element['#localized_options']['attributes']['data-target'] = '#';
      $element['#localized_options']['attributes']['class'][] = 'dropdown-toggle';
      $element['#localized_options']['attributes']['data-toggle'] = 'dropdown';
    }
  }
  // On primary navigation menu, class 'active' is not set on active menu item.
  // @see https://drupal.org/node/1896674
  if (($element['#href'] == $_GET['q'] || ($element['#href'] == '<front>' && drupal_is_front_page())) && (empty($element['#localized_options']['language']))) {
    $element['#attributes']['class'][] = 'active';
  }
  $output = l($element['#title'], $element['#href'], $element['#localized_options']);
  
  return '<li' . drupal_attributes($element['#attributes']) . '>' . $output . $sub_menu . "</li>\n";
}

