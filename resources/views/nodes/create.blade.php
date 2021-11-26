<x-node-layout>
    <x-slot name="header">
        Add Node
    </x-slot>

    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form action="{{route('nodes.store')}}" method="post">
        @csrf
        <div class="flex flex-col space-y-6">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" />
            <label for="node_id">Parent:</label>
            <select id="node_id" name="node_id">
                <option value="">- - -</option>
                @foreach ($nodeList as $node)
                    <option value="{{$node->id}}">
                        @for ($i = 0; $i < $node->depth(); $i++)
                            &nbsp;
                        @endfor
                        {{$node->title}}
                    </option>
                @endforeach
            </select>
            <input class="h-10 hover:bg-gray-500 pointer" type="submit" value="Send"/>
        </div>
    </form>
</x-node-layout>
