<form action="{{route('nodes.store')}}" method="post">
    @csrf
    <input type="text" id="title" name="title" />
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
    <input type="submit" />
</form>
