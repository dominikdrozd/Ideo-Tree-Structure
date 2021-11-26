<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Node extends Model
{
    use HasFactory;

    public $children;

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

    public function deleteWithoutDescendants() {
        if($this->descendants()->count()) {
            DB::table('nodes')
                ->where('node_id', $this->id)
                ->update(['node_id', $this->node_id]);

            DB::table('nodes')
                ->where('path', 'LIKE', $this->path . '.%')
                ->update(['path' => DB::raw("REPLACE(path, '.$this->id.', '.')")]);
        }
        $this->delete();
    }

    public function deleteWithDescendants() {
        $descendants = $this->descendants();

        DB::table('nodes')
            ->where('node_id', $this->id)
            ->update(['node_id' => null]);

        if ($descendants->count()) {
            Node::destroy($descendants);
        }

        return $this->delete();
    }

    public function updateWithPathWithDescendants(array $attributes=[], array $options=[]) {
        $oldPath = $this->path;
        $parent = Node::find($attributes['node_id']);
        $path = '';

        if ($parent){
            $parentPath = str_replace(array("\n","\r\n","\r"), '', $parent->path);
            $path = $parentPath . '.';
        }
        $path .= $this->id;
        $attributes['path'] = $path;

        $this->update($attributes, $options);
        $this->updateDescendants($oldPath);
    }

    protected function updateDescendants(string $oldPath){
        // Update in database.
        DB::table('nodes')
            ->where('path', 'LIKE', $oldPath . '.%')
            ->update(['path' => DB::raw("REPLACE(path, '$oldPath', '$this->path')")]);
    }

    public function loadTree(bool $orderDesc = false) {
        $allNodes = Node::orderBy('title', $orderDesc ? 'DESC' : 'ASC')->get();
        $childrenNodes = $allNodes->where('node_id', $this->id);

        self::formatTree($childrenNodes, $allNodes);
        $this->children = $childrenNodes;
    }

    public static function getParent(int $id) {
        $node = Node::findOrFail($id);
        $parent = Node::findOrFail($node->node_id);

        return $parent;
    }

    public static function getChildren(int $id) {
        $children = Node::where('node_id', $id)->get();

        return $children;
    }

    public static function getDescendants(int $id) {
        $node = Node::findOrFail($id);

        $ancestors = Node::where('path', 'LIKE', $node->path.'.%')->get();
        return $ancestors;
    }

    public static function getAncestors(int $id) {
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

    public static function formatTree(Collection $rootNodes, Collection $allNodes) {
        foreach ($rootNodes as $rootNode) {
            $rootNode->children = $allNodes->where('node_id', $rootNode->id);

            if ($rootNode->children->isNotEmpty()) {
                self::formatTree($rootNode->children, $allNodes);
            }
        }
    }

}
