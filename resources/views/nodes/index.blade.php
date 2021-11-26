<x-node-layout>
    <x-slot name="header">
        List of nodes
    </x-slot>
    @foreach ($tree as $node)
            @include('nodes.components.node', ['node' => $node, 'parent' => null])
    @endforeach

    <script src="/js/script.js"></script>
</x-node-layout>
