@foreach ($tree as $node)
<ul>
    <li>
        {{ $node->title }}
        @if ( $node->children )
            @include('nodes.components.node', ['tree' => $node->children])
        @endif
    </li>
</ul>
@endforeach
