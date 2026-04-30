@foreach($members_fev as $mem)
	<tr>
      	<th scope="row"><input type="checkbox" id="vehicle" onclick="selectGuest({{ $mem->id }}, 'Member')" name="vehicle1"></th>
      	<td>{{ $mem->DisplayName }}</td>
      	<td>{{ $mem->Email }} / {{ $mem->Mobile }}</td>
      	<td> <a href="javascript:" class="fev-active" onclick="favoriteMe({{ $mem->id }})"><i class="fa fa-heart"></i></a> </td>
      	<td>
      		
      	</td>
    </tr>
@endforeach

@foreach($guests_fev as $guest)
	<tr>
      	<th scope="row"><input type="checkbox" id="vehicle" onclick="selectGuest({{ $guest->id }}, 'Guest')" name="vehicle1"></th>
      	<td>{{ $guest->name }}</td>
      	<td>{{ $guest->email }} / {{ $guest->mobile }}</td>
      	<td> <a href="javascript:" class="fev-active" onclick="favoriteMe({{ $guest->id }})"><i class="fa fa-heart"></i></a> </td>
      	<td>
      		<button class="btn-sm btn btn-outline-danger" type="button" onclick="removePlayer({{ $guest->id }})"><i class="fa fa-times" aria-hidden="true"></i></button>
      	</td>
    </tr>
@endforeach