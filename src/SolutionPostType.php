<?php

namespace Xzito\Solutions;

use PostTypes\PostType;

class SolutionPostType {
  public const ID = 'solution';
  public const PLURAL_NAME = 'Solutions';
  public const SINGULAR_NAME = 'Solution';
  public const SLUG = 'solutions';

  private $cpt;
  private $icon = 'dashicons-store';

  public function __construct() {
    $this->create_post_type();

    $this->set_options();
    $this->set_labels();

    $this->cpt->icon($this->icon);
    $this->cpt->register();
  }

  private function create_post_type() {
    $this->cpt = new PostType($this->names());
  }

  private function names() {
    return [
      'name' => self::ID,
      'singular' => self::SINGULAR_NAME,
      'plural' => self::PLURAL_NAME,
      'slug' => self::SLUG,
    ];
  }

  private function set_options() {
    $this->cpt->options([
      'public' => true,
      'show_in_nav_menus' => true,
      'show_in_menu_bar' => false,
      'menu_position' => 21.1,
      'supports' => ['revisions', 'page-attributes'],
      'has_archive' => self::SLUG,
      'rewrite' => ['slug' => self::SLUG, 'with_front' => false],
    ]);
  }

  private function set_labels() {
    $this->cpt->labels([
      'search_items' => self::PLURAL_NAME,
      'archives' => self::PLURAL_NAME,
      'menu_name' => self::PLURAL_NAME,
    ]);
  }
}
