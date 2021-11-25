
    @foreach ($tree as $node)
            @include('nodes.components.node', ['node' => $node])
    @endforeach
