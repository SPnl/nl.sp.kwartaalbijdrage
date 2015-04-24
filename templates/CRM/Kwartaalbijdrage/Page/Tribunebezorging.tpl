{if ($done)}
    <p>{$afdelingen} afdelingen hebben een activiteit tribune bezorging gekregen</p>
{else}
    <p>Genereer tribunebezorg activiteiten voor afdelingen</p>
    <p></p><a href="{crmURL p='civicrm/tribunebezorging' q='action=add' h=0}" class="button">Maak activiteiten aan</a></p>
{/if}