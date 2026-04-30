<select class="form-control game_type" name="game_type_id" onchange="getGameType(this.value)">
    <option value="">Select Game Type</option>
    @foreach($gametypes as $key => $game)
    <option value="{{ $game->id }}" myTag="{{ $game->no_of_players }}">{{ $game->name }}</option>
    @endforeach
</select>