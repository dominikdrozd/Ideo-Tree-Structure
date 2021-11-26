<?php

namespace App\Http\Controllers;

use App\Http\Requests\NodeRequest;
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
    public function store(NodeRequest $request) {
        $validated = $request->validated();
        Node::create($validated);
        return back()->with('success', 'node created.');
    }

    /**
     * Edit form
     *
     * @return void
     */
    public function edit(Node $node) {
        $descendantsIds = $node->descendants()->pluck('id');
        $descendantsIds->push($node->id);

        $nodeList = Node::whereNotIn('id', $descendantsIds)->get();
        return view('nodes.edit', compact('node', 'nodeList'));
    }

    /**
     * Store data after validate.
     *
     * @return void
     */
    public function update(Node $node, NodeRequest $request) {
        $validated = $request->validated();
        $node->updateWithPathWithDescendants($validated);
        return back()->with('success', 'node updated.');
    }

    public function destroy(Node $node) {
        $node->delete();
        return back()->with('success', 'node deleted.');
    }

}
