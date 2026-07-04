<?php

return array (
  'enabled' => true,
  'prefix' => 'docs',
  'middleware' => 
  array (
    0 => 'web',
  ),
  'sidebar' => 
  array (
    'collapsible' => true,
    'max_depth' => 5,
    'show_icons' => true,
  ),
  'toc' => 
  array (
    'enabled' => true,
    'max_level' => 3,
  ),
  'search' => 
  array (
    'enabled' => true,
    'min_length' => 2,
    'max_results' => 20,
  ),
  'pdf' => 
  array (
    'enabled' => true,
    'driver' => 'dompdf',
    'page_size' => 'A4',
    'orientation' => 'portrait',
  ),
  'seo' => 
  array (
    'site_name' => NULL,
    'default_title' => 'Documentation',
    'default_description' => NULL,
  ),
  'reserved_slugs' => 
  array (
    0 => 'docs',
    1 => 'search',
    2 => 'pdf',
    3 => 'learning-paths',
    4 => 'glossary',
  ),
  'embeds' => 
  array (
    'youtube' => true,
    'vimeo' => true,
    'dailymotion' => true,
    'spotify' => true,
    'soundcloud' => true,
    'deezer' => true,
    'apple_podcasts' => true,
  ),
);
