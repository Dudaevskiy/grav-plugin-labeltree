<?php
namespace Grav\Plugin\LabelTree;

use \Grav\Common\Grav;
use \Grav\Common\Page\Collection;
use \Grav\Common\Page\Page;
use \Grav\Common\Page\Pages;

use \Grav\Plugin\LabelTree\Label;

class TreeBuilder
{
    /**
     * Get tree of labels build on collection of all pages.
     *
     * @return Label
     */
    public function getLabelTree(): Label
    {
        $cache = Grav::instance()['cache'];

        $id = hash('md5', serialize('labeltree'));
        $cached = $cache->fetch($id);
        $labelTree = $cached ? $cached['labeltree'] : new Label();

        if (!$labelTree->children) {
            // Get all pages in the site
            $pages = Grav::instance()['pages'];

            // Loop through all pages
            foreach ($pages->all() as $page) {
                if (!isset($page->header()->labels)) {
                    continue;
                }

                $labels = $page->header()->labels;
                if ($labels) {
                    $pageLabels = new Label();
                    $this->convertLabels($pageLabels, $labels, $page);
                    $this->mergeTree($labelTree, $pageLabels->children);
                }
            }

            // Store tree in cache for reuse
            $cache->save($id, ['labeltree' => $labelTree]);
        }

        return $labelTree;
    }

    /**
     * Get pages that contain a hierarchy of labels.
     *
     * @param $param Query parameter used to access blog page. E.g. http://mydomain/blog/labels:label1,label2
     *
     * @return Collection
     */
    public function getPages(string $param = ''): Collection
    {
        if ($param === '') {
            return Grav::instance()['page']->collection();
        }

        $labelTree = $this->getLabelTree();
        $labels = explode(',', $param);

        foreach($labels as $label) {
            $labelTree = $labelTree->children[$label];
        }

        $pages = Grav::instance()['pages']->all();
        $collection = new Collection();
        foreach(array_keys($labelTree->pages) as $path) {
            $collection->addPage($pages[$path]);
        }

        return $collection;
    }

    /**
     * Convert PHP arrays tructure based on Yaml into a Label hierarchy.
     *
     * @param Label $labelTree The resulting converted Label tree
     * @param array $labels PHP array based on Page::header['labels']
     * @param Page $page The Page from loop in getPageTree()
     *
     * @return Collection
     */
    protected function convertLabels(Label $labelTree, array $labels, $page) {
        if (is_array($labels)) {
            foreach($labels as $label) {
                if (is_array($label)) {
                    $key = array_keys($label)[0];
                    $childLabel = new Label($key, $page);
                    $this->convertLabels($childLabel, $label[$key], $page);
                    $labelTree->children[$childLabel->name] = $childLabel;
                } else {
                    $childLabel = new Label($label, $page);
                    $labelTree->children[$label] = $childLabel;
                }
            }
        } else {
            $labelTree->children[$label] = new Label($labels, $page);
        }
    }

    /**
     * Merge label hierarchies from multiple pages into one.
     *
     * @param Label &$labelTree Result of merged page labels.
     * @param Label[] $labels Array of Labels from page
     */
    protected function mergeTree(Label $labelTree, array $labels)
    {
        foreach($labels as $label) {
            if (array_key_exists($label->name, $labelTree->children)) {
                if ($label->children) {
                    $this->mergeTree($labelTree->children[$label->name], $label->children);
                }
                // Add current page to child of $labelTree
                $path = array_keys($label->pages)[0];
                $labelTree->children[$label->name]->pages[$path] = reset($label->pages);
            } else {
                // $label->name does not occur in $labelTree->children
                $path = array_keys($label->pages)[0];
                $label->pages[$path] = reset($label->pages);
                $labelTree->children[$label->name] = $label;
            }
        }
    }
}
