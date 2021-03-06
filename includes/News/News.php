<?php

namespace RRZE\Elements\News;

defined('ABSPATH') || exit;

use function RRZE\Elements\Config\getThemeGroup;

/**
 * [News description]
 */
class News
{
    /**
     * [__construct description]
     */
    public function __construct()
    {
        add_shortcode('custom-news', [$this, 'shortcodeCustomNews']);
        add_shortcode('blogroll', [$this, 'shortcodeCustomNews']);
    }

    /**
     * [shortcodeCustomNews description]
     * @param  array $atts [description]
     * @return string       [description]
     */
    public function shortcodeCustomNews($atts) {
        global $options;
        $sc_atts = shortcode_atts([
            'category' => '',
            'tag' => '',
            'number' => '10',
            'days' => '',
            'id' => '',
            'hide' => '',
            'display' => '',
            'imgfloat' => 'left',
            // aus FAU-Einrichtungen
            'cat'	=> '',
            'num'	=> '',
            'divclass'	=> '',
            'hidemeta'	=> false,
            'hstart'	=> 2,
        ], $atts);
        $sc_atts = array_map('sanitize_text_field', $sc_atts);

        $cat = ($sc_atts['cat'] != '') ? $sc_atts['cat'] : $sc_atts['category'];
        $tag = $sc_atts['cat'];
        $num = ($sc_atts['num'] != '') ? intval($sc_atts['num']) : intval($sc_atts['number']);
        $days = intval($sc_atts['days']);
        $hide = array_map('trim', explode(",", $sc_atts['hide']));
        $display = $sc_atts['display'] == 'list' ? 'list' : '';
        $imgfloat = ($sc_atts['imgfloat'] == 'right') ? 'float-right' : 'float-left';
        $hstart = intval($sc_atts['hstart']);
        $divclass = esc_attr($sc_atts['divclass']);
        $hidemeta = $sc_atts['hidemeta'] == 'true' ? true : false;


        if ($sc_atts['id'] != '') {
            $id = array_map(
                function ($post_ID) {
                    return absint(trim($post_ID));
                },
                explode(",", $sc_atts['id'])
            );
        } else {
            $id = [];
        }

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'orderby' => 'date',
            'posts_per_page' => $num,
            'ignore_sticky_posts' => 1
        ];

        if ($cat != '') {
            $c_id = [];
            $categories = array_map('trim', explode(",", $cat));
            foreach ($categories as $_c) {
                if ($cat_id = get_cat_ID($_c)) {
                    $c_id[] = $cat_id;
                }
            }
            $args['cat'] = implode(',', $c_id);
        }

        if ($tag != '') {
            $t_id = [];
            $tags = array_map('trim', explode(",", $tag));
            foreach ($tags as $_t) {
                if ($term_id = get_term_by('name', $_t, 'post_tag')->term_id) {
                    $t_id[] = $term_id;
                }
            }
            $args['tag__in'] = implode(',', $t_id);
        }

        if ($posts_per_page = absint($num)) {
            $args['posts_per_page'] = $posts_per_page;
        }

        if (absint($days)) {
            $now = current_time('timestamp');
            $timestamp = strtotime('-' . $days . ' days', $now);
            if ($timestamp) {
                $startdate = date('Y-m-d', $timestamp);
                $date_elements = explode('-', $startdate);
                $date_query = [
                    'after' => [
                        'year' => $date_elements[0],
                        'month' => $date_elements[1],
                        'day' => $date_elements[2],
                    ],
                ];
                $args['date_query'] = $date_query;
            }
        }

        if (!empty($id)) {
            $args['post__in'] = $id;
        }

        $output = '';
        $wp_query = new \WP_Query($args);

        $hide_date = in_array('date', $hide);
        if ($hidemeta) {
            $hide[] = 'category';
            $hide[] = 'date';
        }

        /* FAU-Einrichtungen, FAU-Philfak*/

        if ($wp_query->have_posts()) {

            if ($display == 'list') {
                $output .= '<ul class="rrze-elements-news">';
            } else {
                $output .= '<section class="rrze-elements-news blogroll ' . $divclass . '">';
            }

            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                $id = get_the_ID();
                $title = get_the_title();
                $permalink = get_permalink();

                if ($display == 'list') {
                    $output .= '<li>';
                    if (! $hide_date) {
                        $output .= get_the_date('d.m.Y', $id) . ': ';
                    }
                    $output .= '<a href="' . $permalink . '" rel="bookmark">' . $title . '</a>';
                    $output .= '</li>';
                } else {
                    //var_dump(get_stylesheet());
                    switch (getThemeGroup(get_stylesheet())) {
                        case 'fau':
                            if (function_exists('fau_display_news_teaser')) {
                                $output .= fau_display_news_teaser($id, !$hide_date, $hstart, $hidemeta);
                            } else {
                                $output .= $this->display_news_teaser($id, $hide, $hstart, $imgfloat);
                            }
                            break;
                        case 'rrze':
                            if (function_exists('rrze_display_news_teaser')) {
                                $output .= rrze_display_news_teaser($id, $hide, $hstart, $imgfloat);
                            } else {
                                $output .= $this->display_news_teaser($id, $hide, $hstart, $imgfloat);
                            }
                            break;
                        case 'events':
                        default:
                            $output .= $this->display_news_teaser($id, $hide, $hstart, $imgfloat);
                    }
                }
            }

            if ($display == 'list') {
                $output .= '</ul>';
            } else {
                $output .= '</section>';
            }

            wp_reset_postdata();
        } else {
            ?>
            <p><?php $output = __('No posts found.', 'rrze-elements'); ?></p>
            <?php
        }

        wp_enqueue_style('fontawesome');
        wp_enqueue_style('rrze-elements');

        wp_reset_postdata();
        return $output;
    }

    private function display_news_teaser($id = 0, $hide = [], $hstart = 2, $imgfloat = 'float-left') {
        if ($id == 0) return;

        $hide_date = in_array('date', $hide);
        $hide_category = in_array('category', $hide);
        $hide_thumbnail = in_array('thumbnail', $hide);

        $output = '<article id="post-' . $id . '" class="news-item clear clearfix ' . implode(' ', get_post_class()) . ' cf">';
        $output .= '<header class="entry-header">';
        $output .= '<h'.$hstart.' class="entry-title"><a href="' . get_permalink() . '" rel="bookmark">' . get_the_title() . '</a></h'.$hstart.'>';
        $output .= '</header>';
        $output .= '<div class="entry-meta">';
        if (! $hide_date) {
            $output .= '<div class="entry-date">' . get_the_date('d.m.Y', $id) . '</div>';
        }
        if (! $hide_category) {
            $categories = get_the_category($id);
            $separator = " / ";
            $cat_links = [];
            if (!empty($categories)) {
                foreach ($categories as $cat) {
                    $cat_links[] = '<a href="' . esc_url(get_category_link($cat->term_id)) . '" alt="' . esc_attr(sprintf(__('View all posts in %s', 'rrze-elements'), $cat->name)) . '">' . esc_html($cat->name) . '</a>';
                }
                $output .= '<div class="entry-cats">' . implode($separator, $cat_links) . '</div>';
            }
        }
        $output .= '</div>';
        if (has_post_thumbnail($id) && ! $hide_thumbnail) {
            $output .= '<div class="entry-thumbnail ' . $imgfloat . '">' . get_the_post_thumbnail($id, 'post-thumbnail') . '</div>';
        }
        $output .= '<div class="entry-content">' . get_the_excerpt($id) . "</div>";
        $output .= '</article>';

        return $output;
    }
}
