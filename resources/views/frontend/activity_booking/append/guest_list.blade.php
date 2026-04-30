@foreach($guests as $data)
	<tr>
      	<th scope="row"><input type="checkbox" id="vehicle" onclick="selectGuest({{ $data->id }}, 'Guest')" name="vehicle1"></th>
      	<td>{{ $data->name }}</td>
      	<td>{{ $data->email }} / {{ $data->mobile }}</td>
      	<td> 
      		@if($data->is_favorite=='1')
      		<a href="javascript:" class="fev-active" onclick="favoriteMe({{ $data->id }})"><i class="fa fa-heart"></i></a> 
      		@else
      		<a href="javascript:" class="text-secondary" onclick="favoriteMe({{ $data->id }})"><i class="fa fa-heart"></i></a> 
      		@endif
      	</td>
      	<td>
      		<button class="btn-sm btn btn-outline-danger" type="button" onclick="removePlayer({{ $data->id }})"><i class="fa fa-times" aria-hidden="true"></i></button>
      	</td>
    </tr>
@endforeach