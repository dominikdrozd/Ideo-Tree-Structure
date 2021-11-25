<form action="{{route('nodes.update', $node->id)}}" method="post">
    @csrf
    @method('put')
    <input type="text" id="title" value="{{$node->title}}" name="title" />
    <select id="node_id" name="node_id">
        <option value="">- - -</option>
        @foreach ($nodeList as $_node)
            <option value="{{$_node->id}}" {{$node->id === $_node->id ? 'selected' : ''}}>
                @for ($i = 0; $i < $_node->depth(); $i++)
                    &nbsp;
                @endfor
                {{$_node->title}}
            </option>
        @endforeach
    </select>
    <input type="submit" />
</form>
