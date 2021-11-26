@foreach ($tree as $node)
<div class="w-full pl-6 {{$parent ? 'hidden node-'.$parent : ''}}">
    <div class="flex flex-row flex-wrap content-center m-1">
        {{ $node->title }}
        @if ( $node->children->count() )
            <div class="h-6 w-6 flex flex-wrap content-center justify-center ml-2 border border-gray-200 hover:border-gray-400 toggle cursor-pointer" data-toggle={{$node->id}}>+</div>
        @endif
    </div>
    @if ( $node->children->count() )
        @include('nodes.components.node', ['tree' => $node->children, 'parent' => $node->id])
    @endif
</div>
@endforeach
