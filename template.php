<?php
// $Id$

/**
 * Little debuging, it's hard to debug jquery mobile
 * this little wrapper just takes any php objects turns it to json and logs it to the screen.
 * works like a charm :)
 */
function print_to_screen($var) {
  $json = drupal_json_encode($var);
  print "<script>console.log(".$json.")</script>";
}


/**
 * Empty out all the clases.
 */
function jam_class_reset(&$element) {
  if(isset($element['#attributes']['class'])) {
    $element['#attributes']['class'] = array();
  }
}


function jam_fieldset($variables) {
  $element = $variables['element'];
  jam_class_reset($element);
  element_set_attributes($element, array('id'));
  _form_set_class($element, array('form-wrapper'));
  
  if (!empty($element['#collapsible'])) {
    $element['#attributes']['data-role'] = 'collapsible';
  }
  if (!empty($element['#collapsed'])) {
    $element['#attributes']['data-collapsed'] = 'true';
  }
   
  
  $output = '<fieldset' . drupal_attributes($element['#attributes']) . '>';
  if (!empty($element['#title'])) {
    // Always wrap fieldset legends in a SPAN for CSS positioning.
    $output .= '<h3>' . $element['#title'] . '</h3>';
  }
  $output .= '<div class="ui-body ui-body-c">';
  if (!empty($element['#description'])) {
    $output .= '<div class="fieldset-description">' . $element['#description'] . '</div>';
  }
  $output .= $element['#children'];
  if (isset($element['#value'])) {
    $output .= $element['#value'];
  }
  $output .= '</div>';
  $output .= "</fieldset>\n";
  return $output;
}

function jam_admin_block_content($variables) {
  $content = $variables['content'];
  $output = '';
  

  
  if (!empty($content)) {
    $compact = system_admin_compact_mode();
    
    // This sucks but I have no other way of knowing how to tell admin apart from admin/config
    if (!arg(1) || arg(1) == 'structure') {
      $output .= '<ul data-role="listview" data-inset="true">';
    }

    foreach ($content as $item) {
      if (!$compact && isset($item['description'])) {
        $output .= '<li><h3>' . l($item['title'], $item['href'], $item['localized_options']) .'</h3><p>'. filter_xss_admin($item['description']). '</p></li>';        
      }
      else {
        $output .= '<li>' . l($item['title'], $item['href'], $item['localized_options']) . '</li>';        
      }

    }

    // !todo: find a better way of doing this.
    if (!arg(1) || arg(1) == 'structure') {
      $output .= '</ul>';
    }
  }
  return $output;
}

function jam_admin_page($variables) {
  $blocks = $variables['blocks'];

  $stripe = 0;
  $container = array();
  
  foreach ($blocks as $block) {
    if ($block_output = theme('admin_block', array('block' => $block))) {
      $container[] = $block_output;
    }
  }

  $output .= '<div>';
  $output .= theme('system_compact_link');

  foreach ($container as $id => $data) {
    $output .= $data;
  }
  $output .= '</div>';
  return $output;
}

function jam_admin_block($variables) {
  $block = $variables['block'];
  $output = '';

  // Don't display the block if it has no content to display.
  if (empty($block['show'])) {
    return $output;
  }

  if (!empty($block['title'])) {
    $title .= '<li data-role="list-divider" role="heading">' . $block['title'] . '</li>';
  }
  
  $output .= '<ul data-role="listview" data-inset="true">';
  
  if (!empty($block['content'])) {
    if($title) {
      $output .= $title;
    }
    $output .= '' . $block['content'] . '';
  }
  else {
    $output .= '<div class="description">' . $block['description'] . '</div>';
  }
  
  $output .= '</ul>';
  

  return $output;
}

function jam_system_admin_index($variables) {
  $menu_items = $variables['menu_items'];

  // Iterate over all modules.
  foreach ($menu_items as $module => $block) {
    list($description, $items) = $block;

    // Output links.
    if (count($items)) {
      $block = array();
      $block['title'] = $module;
      $block['content'] = theme('admin_block_content', array('content' => $items));
      $block['description'] = t($description);
      $block['show'] = TRUE;

      if ($block_output = theme('admin_block', array('block' => $block))) {
        $container[] .= $block_output;
      }
    }
  }

  $output = '<div class="admin clearfix">';
  $output .= theme('system_compact_link');
  foreach ($container as $id => $data) {
    $output .= '<div class="' . $id . ' clearfix">';
    $output .= $data;
    $output .= '</div>';
  }
  $output .= '</div>';

  return $output;
}

function jam_menu_local_task($variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  if (!empty($variables['element']['#active'])) {
    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $active = '';
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title!active', array('!local-task-title' => $link['title'], '!active' => $active));
  }
  if (!empty($variables['element']['#active'])) {
    $link['localized_options']['class'][] = 'ui-btn-active';
    $link = l($link_text, $link['href'], $link['localized_options']);
  }
  else {
    $link = l($link_text, $link['href'], $link['localized_options']);
  }
  
  return '<li>' . $link . "</li>\n";
}

function jam_menu_local_tasks(&$variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<ul>';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<ul>';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Returns HTML for a single local action link.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: A render element containing:
 *     - #link: A menu link array with 'title', 'href', and 'localized_options'
 *       keys.
 *
 * @ingroup themeable
 */
function jam_menu_local_action($variables) {
  $link = $variables['element']['#link'];
  
  if (isset($link['localized_options'])) {
    $link['localized_options']['attributes'] = array('data-rel' => 'dialog', "data-transition" => "pop");
    $options = $link['localized_options'];
  }
  else {
    $options = array('attributes' => array('data-rel' => 'dialog', "data-transition" => "pop"));
  }

  $output = '<li>';
  if (isset($link['href'])) {
    $output .= l($link['title'], $link['href'], $options);
  }
  elseif (!empty($link['localized_options']['html'])) {
    $output .= $link['title'];
  }
  else {
    $output .= check_plain($link['title']);
  }
  $output .= "</li>\n";

  return $output;
}

function jam_form_element($variables) {
  $element = &$variables['element'];
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  $attributes['class'] = array();
  if (!empty($element['#type'])) {
  }
  if (!empty($element['#name'])) {
  }
  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
  }
  $output = '<div data-role="fieldcontain" ' . drupal_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
  }
  $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
  $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';

  switch ($element['#title_display']) {
    case 'before':
    case 'invisible':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      $output .= ' ' . $prefix . $element['#children'] . $suffix;
      $output .= ' ' . theme('form_element_label', $variables) . "\n";
      break;

    case 'none':
    case 'attribute':
      // Output no label and no required marker, only the children.
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;
  }

  $output .= "</div>\n";

  return $output;
}

/**
 * Overwriting theme_element_label
 *
 * Removed the appending of a asterisk for required elements.
 * We will not add any visual indicators to test the "required" element option.
 */
function jam_form_element_label($variables) {
  $element = $variables['element'];
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // If title and required marker are both empty, output no label.
  if (empty($element['#title']) && empty($element['#required'])) {
    return '';
  }

  $title = filter_xss_admin($element['#title']);

  $attributes = array();

  if (!empty($element['#id'])) {
    $attributes['for'] = $element['#id'];
  }

  // The leading whitespace helps visually separate fields from inline labels.
  return ' <label' . drupal_attributes($attributes) . '>' . $t('!title', array('!title' => $title)) . "</label>\n";
}

/**
 * Overwritten the default textfield theme.
 */
function jam_textfield($variables) {
  $element = $variables['element'];
  $element['#attributes']['type'] = 'text';
  element_set_attributes($element, array('id', 'name', 'value', 'size', 'maxlength'));
  _form_set_class($element, array('form-text'));

  $extra = '';
  if ($element['#autocomplete_path'] && drupal_valid_path($element['#autocomplete_path'])) {
    drupal_add_library('system', 'drupal.autocomplete');
    $element['#attributes']['class'][] = 'form-autocomplete';

    $attributes = array();
    $attributes['type'] = 'hidden';
    $attributes['id'] = $element['#attributes']['id'] . '-autocomplete';
    $attributes['value'] = url($element['#autocomplete_path'], array('absolute' => TRUE));
    $attributes['disabled'] = 'disabled';
    $attributes['class'][] = 'autocomplete';
    $extra = '<input' . drupal_attributes($attributes) . ' />';
  }
  if (isset($element['#description'])) {
    // Lets move it to placeholder, is not correct but the space is limited on a mobile device.
    $element['#attributes']['placeholder'] = $element['#description'];
  }
  
  if (isset($element['#required']) && $element['#required'] == TRUE) {
    // Lets add the required attribute to the element.
    $element['#attributes']['required'] = "required";
  }

  $output = '<input' . drupal_attributes($element['#attributes']) . ' />';

  return $output . $extra;
}