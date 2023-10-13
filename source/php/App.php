<?php

namespace SchoolsManager;

use SchoolsManager\API\Api;
use SchoolsManager\API\Fields\FieldsRegistrar;
use SchoolsManager\API\Fields\SchoolPagesField;
use SchoolsManager\Entity\PostType;
use SchoolsManager\MetaBox\SchoolPagesMetaBox;
use SchoolsManager\MetaBox\SchoolPagesMetaBoxCallback;
use SchoolsManager\PostType\ElementarySchool\ElementarySchoolConfiguration;
use SchoolsManager\PostType\Person\Person;
use SchoolsManager\PostType\Person\PersonConfiguration;
use SchoolsManager\PostType\PreSchool\PreSchoolConfiguration;
use SchoolsManager\Taxonomy\GeographicArea\GeographicArea;
use SchoolsManager\Taxonomy\Grade\Grade;
use SchoolsManager\Taxonomy\Usp\Usp;

class App
{
    public function __construct()
    {

        add_action('plugins_loaded', array( $this, 'init' ));

        add_filter('rest_prepare_taxonomy', array($this, 'respectMetaBoxCbInGutenberg' ), 10, 3);

        add_action('plugins_loaded', array( $this, 'useGoogleApiKeyIfDefined' ));

        add_action('acf/save_post', array($this, 'saveCustomExcerptField'), 20, 1);
    }


    /**
     * Initializes the App by registering the API,
     * Admin, Post Types, Meta Boxes and Taxonomies.
     *
     * @return void
     */
    public function init()
    {
        $apiFields          = [new SchoolPagesField()];
        $apiFieldsRegistrar = new FieldsRegistrar($apiFields);

        //General
        $api = new Api($apiFieldsRegistrar);
        $api->addHooks();

        $admin = new Admin();
        $admin->addHooks();

        /**
         * Post type: Pre school
         */
        $preSchoolPostTypeConfiguration = new PreSchoolConfiguration();
        $preSchoolPostTypeArgs          = $preSchoolPostTypeConfiguration->getPostTypeArgs();
        $preSchoolPostType              = new PostType(...array_values($preSchoolPostTypeArgs));
        $preSchoolPostType->addHooks();

        /**
         * Post type: Elementary school
         */
        $elementarySchoolPostTypeConfiguration = new ElementarySchoolConfiguration();
        $elementarySchoolPostTypeArgs          = $elementarySchoolPostTypeConfiguration->getPostTypeArgs();
        $elementarySchoolPostType              = new PostType(...array_values($elementarySchoolPostTypeArgs));
        $elementarySchoolPostType->addHooks();

        $person = new Person(...array_values(PersonConfiguration::getPostTypeArgs()));
        $person->addHooks();

        //Meta boxes
        $schoolPagesCallbackRenderer = new SchoolPagesMetaBoxCallback();
        $schoolPagesMetaBox          = new SchoolPagesMetaBox($schoolPagesCallbackRenderer);
        $schoolPagesMetaBox->addHooks();

        // Taxonomies
        $sharedArguments = [
        'meta_box_cb' => false
        ];

        $taxonomyConfigurations = [
        [
            GeographicArea::class,
            __('Areas', ASM_TEXT_DOMAIN),
            __('Area', ASM_TEXT_DOMAIN),
            'area',
            ['elementary-school', 'pre-school'],
            []
        ],
        [
            Specialization::class,
            __('Specializations', ASM_TEXT_DOMAIN),
            __('Specialization', ASM_TEXT_DOMAIN),
            'specialization',
            ['elementary-school', 'pre-school'],
            []
        ],
        [
            Grade::class,
            __(
                'Grades',
                ASM_TEXT_DOMAIN
            ),
            __(
                'Grade',
                ASM_TEXT_DOMAIN
            ),
            'grade',
            ['elementary-school'],
            []
        ],
        [
            Profile::class,
            __(
                'Profiles',
                ASM_TEXT_DOMAIN
            ),
            __(
                'Profile',
                ASM_TEXT_DOMAIN
            ),
            'profile',
            ['elementary-school', 'pre-school'],
            []
        ],
        ];

        $taxonomies = [];

        foreach ($taxonomyConfigurations as $config) {
            list($class,
            $plural,
            $singular,
            $slug,
            $postType,
            $uniqueArgs
            )             = $config;
            $args         = array_merge($sharedArguments, $uniqueArgs);
            $taxonomies[] = new $class($plural, $singular, $slug, $postType, $args);
        }

        foreach ($taxonomies as $taxonomy) {
            $taxonomy->registerTaxonomy();
        }
    }

    /**
     * This method is used to modify the response object
     * for the REST API request to respect the meta box
     * callback in Gutenberg.
     *
     * @param WP_REST_Response $response The response object for the REST API request.
     * @param WP_Taxonomy $taxonomy The taxonomy object.
     * @param WP_REST_Request $request The REST API request object.
     *
     * @return WP_REST_Response The modified response object.
     */
    public function respectMetaBoxCbInGutenberg($response, $taxonomy, $request)
    {
        $context = ! empty($request['context']) ? $request['context'] : 'view';

            // Context is edit in the editor
        if ($context === 'edit' && $taxonomy->meta_box_cb === false) {
            $data_response = $response->get_data();

            $data_response['visibility']['show_ui'] = false;

            $response->set_data($data_response);
        }

            return $response;
    }

    /**
     * Sets the Google API key if it is defined.
     *
     * If the constant 'GOOGLE_API_KEY' is defined,
     * this method sets the Google API key using the 'acf_update_setting' function.
     *
     * @return void
     */
    public function useGoogleApiKeyIfDefined(): void
    {
        if (defined('GOOGLE_API_KEY')) {
            \acf_update_setting('google_api_key', \GOOGLE_API_KEY);
        }
    }


    /**
     * Saves the custom excerpt field for a given post ID.
     *
     * @param int $postId The ID of the post to save the custom excerpt for.
     * @return void
     */
    public function saveCustomExcerptField($postId): void
    {

        $customExcerpt = \get_field('custom_excerpt', $postId);

        remove_action('acf/save_post', array($this, 'saveCustomExcerptField'), 20, 1);
        wp_update_post(['ID' => $postId, 'post_excerpt' => $customExcerpt], false);
        add_action('acf/save_post', array($this, 'saveCustomExcerptField'), 20, 1);
    }
}
