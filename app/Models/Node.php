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

    /**
     * Calculate depth of object in tree.
     *
     * @return int
     */
    public function depth() {
        $depth = explode('.', $this->path);
        return count($depth);
    }

    /**
     * Get parent node.
     *
     * @return Node
     */
    public function parent() {
        return self::getParent($this->id);
    }

    /**
     * Get collection of object children.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function children() {
        return self::getChildren($this->id);
    }

    /**
     * Get collection of object ancestors.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function ancestors() {
        return self::getAncestors($this->id);
    }

    /**
     * Get collection of object descendants.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function descendants() {
        return self::getDescendants($this->id);
    }

    /**
     * Delete node without his descendants.
     *
     * @return bool|null
     */
    public function deleteWithoutDescendants() {
        if($this->descendants()->count()) {
            DB::table('nodes')
                ->where('node_id', $this->id)
                ->update(['node_id', $this->node_id]);

            DB::table('nodes')
                ->where('path', 'LIKE', $this->path . '.%')
                ->update(['path' => DB::raw("REPLACE(path, '.$this->id.', '.')")]);
        }
        return $this->delete();
    }

    /**
     * Delete node with his descendants.
     *
     * @return bool|null
     */
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

    /**
     * Update nodes with their descendants.
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
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

        $this->updateDescendants($oldPath, $path);
        return $this->update($attributes, $options);
    }

    /**
     * Update node path.
     *
     * @param string $oldPath
     * @param string $newPath
     * @return int
     */
    protected function updateDescendants(string $oldPath, string $newPath) {
        // Update in database.
        return DB::table('nodes')
            ->where('path', 'LIKE', $oldPath . '.%')
            ->update(['path' => DB::raw("REPLACE(path, '$oldPath', '$newPath')")]);
    }

    /**
     * Prepare node tree.
     *
     * @param boolean $orderDesc
     * @return void
     */
    public function loadTree(bool $orderDesc = false) {
        $allNodes = Node::orderBy('title', $orderDesc ? 'DESC' : 'ASC')->get();
        $childrenNodes = $allNodes->where('node_id', $this->id);

        self::formatTree($childrenNodes, $allNodes);
        $this->children = $childrenNodes;
    }

    /**
     * Get parent node.
     *
     * @param integer $id
     * @return Node
     */
    public static function getParent(int $id) {
        $parent = Node::findOrFail(['node_id' => $id]);
        return $parent;
    }

    /**
     * Get children nodes.
     *
     * @param integer $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getChildren(int $id) {
        $children = Node::where('node_id', $id)->get();

        return $children;
    }


    /**
     * Get descendant nodes.
     *
     * @param integer $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getDescendants(int $id) {
        $node = Node::findOrFail($id);

        $ancestors = Node::where('path', 'LIKE', $node->path.'.%')->get();
        return $ancestors;
    }

    /**
     * Get ancestor nodes.
     *
     * @param integer $id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAncestors(int $id) {
        $node = Node::findOrFail($id);
        $ids = explode('.', $node->path);
        array_pop($ids);

        $descendants = Node::WhereIn('id', $ids)->get();
        return $descendants;
    }

    /**
     * Load full tree of nodes.
     *
     * @param boolean $orderDesc
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTree(bool $orderDesc = false) {
        $allNodes = Node::orderBy('title', $orderDesc ? 'DESC' : 'ASC')->get();
        $rootNodes = $allNodes->whereNull('node_id');

        self::formatTree($rootNodes, $allNodes);
        return $rootNodes;
    }

    /**
     * Format tree structure.
     *
     * @param \Illuminate\Database\Eloquent\Collection $rootNodes
     * @param \Illuminate\Database\Eloquent\Collection $allNodes
     * @return void
     */
    public static function formatTree(Collection $rootNodes, Collection $allNodes) {
        foreach ($rootNodes as $rootNode) {
            $rootNode->children = $allNodes->where('node_id', $rootNode->id);

            if ($rootNode->children->isNotEmpty()) {
                self::formatTree($rootNode->children, $allNodes);
            }
        }
    }

}
