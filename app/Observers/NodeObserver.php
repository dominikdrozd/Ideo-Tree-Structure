<?php

namespace App\Observers;

use App\Models\Node;

class NodeObserver
{
    /**
     * Handle the Node "created" event.
     *
     * @param  \App\Models\Node  $node
     * @return void
     */
    public function created(Node $node)
    {
        if($node->node_id === null) {
            $node->update(['path' => $node->id]);
        } else {
            $parent = Node::find($node->node_id);
            $node->update(['path' => $parent->path . '.' . $node->id]);
        }
    }

    /**
     * Handle the Node "updated" event.
     *
     * @param  \App\Models\Node  $node
     * @return void
     */
    public function updated(Node $node)
    {
    }

    /**
     * Handle the Node "deleted" event.
     *
     * @param  \App\Models\Node  $node
     * @return void
     */
    public function deleted(Node $node)
    {
        //
    }

    /**
     * Handle the Node "restored" event.
     *
     * @param  \App\Models\Node  $node
     * @return void
     */
    public function restored(Node $node)
    {
        //
    }

    /**
     * Handle the Node "force deleted" event.
     *
     * @param  \App\Models\Node  $node
     * @return void
     */
    public function forceDeleted(Node $node)
    {
        //
    }
}
