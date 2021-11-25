<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Node extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'path',
        'node_id'
    ];

    public function depth() {
        $depth = explode('.', $this->path);
        return count($depth);
    }

    public function parent() {
        return self::getParent($this->id);
    }

    public function children() {
        return self::getChildren($this->id);
    }

    public function ancestors() {
        return self::getAncestors($this->id);
    }

    public function descendants() {
        return self::getDescendants($this->id);
    }

    public function loadTree(bool $orderDesc = false) {
        $allNodes = Node::orderBy('title', $orderDesc ? 'DESC' : 'ASC')->get();
        $childrenNodes = $allNodes->where('node_id', $this->id);

        self::formatTree($childrenNodes, $allNodes);
        $this->children = $childrenNodes;
    }

    public static function getParent($id) {
        $node = Node::findOrFail($id);
        $parent = Node::findOrFail($node->node_id);

        return $parent;
    }

    public static function getChildren($id) {
        $children = Node::where('node_id', $id)->get();

        return $children;
    }

    public static function getAncestors($id) {
        $node = Node::findOrFail($id);

        $ancestors = Node::where('path', 'LIKE', $node->path.'.%')->get();
        return $ancestors;
    }

    public static function getDescendants($id) {
        $node = Node::findOrFail($id);
        $ids = explode('.', $node->path);
        array_pop($ids);

        $descendants = Node::WhereIn('id', $ids)->get();
        return $descendants;
    }

    public static function getTree(bool $orderDesc = false) {
        $allNodes = Node::orderBy('title', $orderDesc ? 'DESC' : 'ASC')->get();
        $rootNodes = $allNodes->whereNull('node_id');

        self::formatTree($rootNodes, $allNodes);
        return $rootNodes;
    }

    public static function formatTree($rootNodes, $allNodes) {
        foreach ($rootNodes as $rootNode) {
            $rootNode->children = $allNodes->where('node_id', $rootNode->id);

            if ($rootNode->children->isNotEmpty()) {
                self::formatTree($rootNode->children, $allNodes);
            }
        }
    }

}
