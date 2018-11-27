<?php
namespace Grav\Plugin\LabelTree;

use \Grav\Common\Grav;
use \Grav\Common\Page\Page;

class Label
{
    /** @var string */
    public $name = '';
    /** @var label[] */
    public $children = [];
    /** @var array */
    public $pages = [];

    public function __construct(string $name = '', Page $page = null)
    {
        $this->name = $name;

        if ($page) {
            $this->pages[$page->path()] = ['slug' => $page->slug()];
        }
     }
}
