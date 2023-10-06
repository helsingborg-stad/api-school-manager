<?php

namespace SchoolsManager\API\Fields;

use SchoolsManager\Entity\API\Field;
use SchoolsManager\Entity\API\FieldGetCallback;
use SchoolsManager\PostType\ElementarySchool\ElementarySchoolConfiguration;
use SchoolsManager\PostType\PreSchool\PreSchoolConfiguration;
use WP_REST_Request;

class SchoolPagesField extends Field
{
    use FieldGetCallback;

    public string|array $objectType;
    public string $attribute = 'pages';

    public function __construct()
    {
        $this->objectType = [
            PreSchoolConfiguration::POST_TYPE_SLUG,
            ElementarySchoolConfiguration::POST_TYPE_SLUG
        ];
    }

    public function getCallback(string|array $object, string $field_name, WP_REST_Request $request): array
    {
        return get_posts(
            array(
                'post_type'      => 'page',
                'fields'         => 'ids',
                'posts_per_page' => -1,
                'meta_key'       => 'parent_school',
                'meta_value'     => $object['id'],
                'post_type__in'  => array('page'),
            )
        );
    }
}
