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
