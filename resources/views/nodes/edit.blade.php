<x-node-layout>
    <x-slot name="header">
        Edit Node {{$node->title}}
    </x-slot>

    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form action="{{route('nodes.update', $node->id)}}" method="post">
        @csrf
        @method('put')
        <div class="flex flex-col space-y-6">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="{{$node->title}}"/>
            <label for="node_id">Parent:</label>
            <select id="node_id" name="node_id">
                <option value="">- - -</option>
                @foreach ($nodeList as $_node)
                    <option value="{{$_node->id}}" {{$node->node_id == $_node->id ? 'selected' : ''}}>
                        @for ($i = 0; $i < $_node->depth(); $i++)
                            &nbsp;
                        @endfor
                        {{$_node->title}}
                    </option>
                @endforeach
            </select>
            <input class="h-10 hover:bg-gray-500 pointer" type="submit" value="Send"/>
        </div>
    </form>
</x-node-layout>
