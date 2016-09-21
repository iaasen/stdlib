<?php
namespace Oppned;

use Oppned\View\Helper\MessageWidget;

return [
    'view_helpers' => [
        'invokables' => [
            'messages' => MessageWidget::class,
        ],
    ],
];
