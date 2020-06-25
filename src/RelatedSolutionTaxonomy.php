<?php

namespace Xzito\Solutions;

use PostTypes\Taxonomy;

class RelatedSolutionTaxonomy {
  public const POST_TYPE = 'attachment';
  public const PLURAL_NAME = 'Related Solutions';
  public const SINGULAR_NAME = 'Related Solution';

  private $taxonomy;

  public function __construct() {
    $this->taxonomy = new Taxonomy($this->names());
    $this->set_options();
    $this->set_post_type();
    $this->taxonomy->register();

    add_action('admin_init', [$this, 'cast_taxonomy_terms'], PHP_INT_MAX);
  }

  public function name() {
    return $this->name;
  }

  public function cast_taxonomy_terms() {
    $terms = $_POST['tax_input'][Solutions::TAXONOMY_ID] ?? '';

    if ($terms) {
      $_POST['tax_input'][Solutions::TAXONOMY_ID] = array_map('intval', $terms);
    }
  }

  private function names() {
    return [
      'name' => Solutions::TAXONOMY_ID,
      'singular' => self::SINGULAR_NAME,
      'plural' => self::PLURAL_NAME,
      'slug' => Solutions::TAXONOMY_ID,
    ];
  }

  private function set_options() {
    $this->taxonomy->options([
      'publicly_queryable' => false,
      'show_ui' => false,
      'show_in_nav_menus' => false,
      'show_tagcloud' => false,
      'show_in_quick_edit' => false,
      'show_admin_column' => false,
      'hierarchical' => false,
      'meta_box_cb' => false,
    ]);
  }

  private function set_post_type() {
    $this->taxonomy->posttype(self::POST_TYPE);
  }
}
