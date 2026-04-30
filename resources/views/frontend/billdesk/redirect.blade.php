<form method="post" action="{{ $txnUrl }}">
    <input type="hidden" name="msg" value="{{ $msg }}">
    <button type="submit" style="display:none;">Pay</button>
</form>

<script>
    document.forms[0].submit();
</script>
