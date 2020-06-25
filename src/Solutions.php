<?php

namespace Xzito\Solutions;
class Solutions {
  public const QUERY_ARG = 'filter-by-solution';
  public const TAXONOMY_ID = 'related_solution';

  public static function all($options = ['return_type' => 'object']) {
    $return_type = self::set_return_type($options);

    $query = new \WP_Query([
      'nopaging' => true,
      'post_type' => SolutionPostType::ID,
      'fields' => 'ids',
    ]);

    $solution_ids = $query->posts ?? [];

    if ($return_type === 'ids') {
      $solutions = $solution_ids;
    } else {
      $solutions = [];

      array_map(function ($id) use (&$solutions) {
        $solutions[] = new Solution($id);
      }, $solution_ids);
    }

    return $solutions;
  }

  private static function set_return_type($options) {
    $return_types = ['id', 'name', 'slug', 'object'];
    $return_type = $options['return_type'];

    if (!in_array($return_type, $return_types)) {
      $return_type = 'object';
    }

    return $return_type;
  }

  private static function filter_query_arg() {
    return $_REQUEST[self::QUERY_ARG] ?? false;
  }

  private static function selected($filtered_slug, $solution_slug) {
    if ($filtered_slug === $solution_slug) {
      $markup = 'selected="selected"';
    }

    return $markup ?? '';
  }

  private static function in_media_library() {
    return (get_current_screen()->base === 'upload' ? true : false);
  }

  public function __construct() {
    add_action('plugins_loaded', [$this, 'create_options_page']);
    add_action('init', [$this, 'create_post_type'], 0);
    add_action('init', [$this, 'create_taxonomy'], 0);
    add_action('init', [$this, 'create_bulk_action'], 0);
    add_action('init', [$this, 'create_terms'], 10);
    add_action('restrict_manage_posts', [$this, 'create_filters']);
    add_action('acf/save_post', [$this, 'set_fields_on_save'], 20);
    add_action('wp_trash_post', [$this, 'destroy_terms']);

    add_filter('parse_query', [$this, 'filter_query']);
  }

  public function create_post_type() {
    new SolutionPostType();
  }

  public function create_taxonomy() {
    new RelatedSolutionTaxonomy();
  }

  public function create_bulk_action() {
    new BulkAction();
  }

  public function create_options_page() {
    $page_title = SolutionPostType::PLURAL_NAME . ' Page';
    $menu_title = SolutionPostType::PLURAL_NAME . ' Page';
    $parent_slug = 'edit.php?post_type=' . SolutionPostType::ID;

    if (function_exists('acf_add_options_sub_page')) {
      acf_add_options_sub_page([
        'page_title' => $page_title,
        'menu_title' => $menu_title,
        'parent_slug' => $parent_slug,
      ]);
    }
  }

  public function create_terms() {
    foreach (self::all() as $solution) {
      $this->create_related_solution_term($solution);
    }
  }

  public function create_filters($post_type) {
    if (!self::in_media_library()) {
      return;
    }

    $filtered_slug = self::filter_query_arg() ?? '';

    $solutions = Solutions::all();
    sort($solutions);

    $options = [];

    array_map(function ($solution) use (&$options, $filtered_slug) {
      $selected = self::selected($filtered_slug, $solution->slug());
      $slug = $solution->slug();
      $name = $solution->name();

      $options[] = "<option value=\"$slug\" $selected>$name</option>";
    }, $solutions);

    $markup = '<select name=' . self::QUERY_ARG . '>';
    $markup .= '<option value="0">All solutions</option>';
    $markup .= implode($options);
    $markup .= '</select>';
    $markup .= '&nbsp;';

    echo $markup;
  }

  public function filter_query($query) {
    if (!(is_admin() && $query->is_main_query())) {
      return $query;
    }

    if (!self::in_media_library()) {
      return $query;
    }

    if (!self::filter_query_arg()) {
      return $query;
    }

    if (self::filter_query_arg() === '0') {
      return $query;
    }

    $query->query_vars['tax_query'] = [
      [
        'taxonomy' => Solutions::TAXONOMY_ID,
        'field' => 'slug',
        'terms' => self::filter_query_arg(),
      ],
    ];

    return $query;
  }

  public function set_fields_on_save($post_id) {
    if (!$this->will_set_on_save($post_id)) {
      return;
    }

    $solution = new Solution($post_id);

    $this->set_post_data($solution);
    $this->set_post_thumbnail($solution);
    $this->create_related_solution_term($solution);
  }

  public function destroy_terms($post_id) {
    if (!$this->will_set_on_save($post_id)) {
      return;
    }

    $solution = new Solution($post_id);

    $this->delete_related_solution_term($solution);
  }

  private function will_set_on_save($id) {
    return (get_post_type($id) == SolutionPostType::ID ? true : false);
  }

  private function set_post_data($solution) {
    wp_update_post([
      'ID' => $solution->id(),
      'post_name' => $solution->slug(),
      'post_title' => $solution->name(),
    ]);
  }

  private function set_post_thumbnail($solution) {
    set_post_thumbnail($solution->id(), $solution->card_image());
  }

  private function create_related_solution_term($solution) {
    if (get_post_status($solution->id()) === 'publish') {
      wp_insert_term($solution->name(), self::TAXONOMY_ID);
    }
  }

  private function delete_related_solution_term($solution) {
    $term = get_term_by('name', $solution->name(), self::TAXONOMY_ID);

    wp_delete_term($term->term_id, self::TAXONOMY_ID);
  }
}
