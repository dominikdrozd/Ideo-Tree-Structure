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
        return self::getDescendants($this->id)->count();
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

    // TODO
    public static function refreshPaths() {

    }

}
