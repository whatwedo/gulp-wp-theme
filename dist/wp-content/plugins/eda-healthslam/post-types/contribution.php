<?php
/*
 * Copyright (c) 2015, whatwedo GmbH
 * All rights reserved
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation 
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */
 /**
 * @author Ueli Banholzer <ueli@whatwedo.ch>
 */
class EDA_HealthSlam_PostType_Contribution
{
    public function __construct()
    {
        // register actions
        add_action('init', array(&$this, 'wpInit'));
    }

    public function wpInit()
    {
        $this->create();
    }

    protected function create()
    {
        $labels = array(
            'name'                => _x( 'Eingaben', 'eda_healthslam' ),
            'singular_name'       => _x( 'Eingabe', 'eda_healthslam' ),
            'menu_name'           => __( 'Eingaben', 'eda_healthslam' ),
            'parent_item_colon'   => __( 'Übergeordnete Eingabe', 'eda_healthslam' ),
            'all_items'           => __( 'Alle Eingaben', 'eda_healthslam' ),
            'view_item'           => __( 'Eingabe ansehen', 'eda_healthslam' ),
            'add_new_item'        => __( 'Neuer Eingabe hinzufügen', 'eda_healthslam' ),
            'add_new'             => __( 'Neu', 'eda_healthslam' ),
            'edit_item'           => __( 'Bearbeiten', 'eda_healthslam' ),
            'update_item'         => __( 'Speichern', 'eda_healthslam' ),
            'search_items'        => __( 'Suchen', 'eda_healthslam' ),
            'not_found'           => __( 'Nicht gefunden', 'eda_healthslam' ),
            'not_found_in_trash'  => __( 'Nicht gefunden', 'eda_healthslam' ),
        );
        $args = array(
            'label'               => __( 'contribution', 'eda_healthslam' ),
            'description'         => __( 'Eingaben', 'eda_healthslam' ),
            'labels'              => $labels,
            'supports'            => [
                'title',
                'thumbnail',
            ],
            'taxonomies'          => [],
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'can_export'          => true,
            'has_archive'         => 'contributions',
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
        );

        register_post_type('contribution', $args);
    }
}
