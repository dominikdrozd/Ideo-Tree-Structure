<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{

    /**
     * Return the tree.
     *
     * @return void
     */
    public function index(Request $request) {
        $tree = Node::getTree($request->boolean('orderDesc'));
        return view('nodes.index', compact('tree'));
    }

    /**
     * Show part of node.
     *
     * @return void
     */
    public function show(Node $node, Request $request) {
        $tree = $node->loadTree($request->boolean('orderDesc'));
        return view('nodes.show', compact('tree'));
    }

    /**
     * Create form
     *
     * @return void
     */
    public function create() {
        $nodeList = Node::all();
        return view('nodes.create', compact('nodeList'));
    }

    /**
     * Store data after validate.
     *
     * @return void
     */
    public function store() {
        // TODO SAVE
    }

    /**
     * Edit form
     *
     * @return void
     */
    public function edit(Node $node) {
        $nodeList = Node::all();
        return view('nodes.create', compact('node', 'nodeList'));
    }

    /**
     * Store data after validate.
     *
     * @return void
     */
    public function update() {
        // TODO Save
    }

}
