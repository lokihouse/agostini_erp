<?php

return [
    'resources' => [
        'label'                  => 'Registro de Atividade',
        'plural_label'           => 'Registros de Atividades',
        'navigation_item'        => true,
        'navigation_group'       => 'Sistema',
        'navigation_icon'        => 'heroicon-o-document-text',
        'navigation_sort'        => 10,
        'default_sort_column'    => 'id',
        'default_sort_direction' => 'desc',
        'navigation_count_badge' => false,
        'resource'               => \Rmsramos\Activitylog\Resources\ActivitylogResource::class,
    ],
    'datetime_format' => 'd/m/Y H:i:s',
];
