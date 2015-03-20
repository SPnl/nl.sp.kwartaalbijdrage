<h2>Kwartaalbijdrages</h2>

<table>
    <thead>
        <tr>
            <th>Datum</th>
            <th>Basisvergoeding</th>
            <th>Aantal leden</th>
            <th></th>
            <th>Ledenvergoeding</th>
            <th>Bezorgde tribunes</th>
            <th>Waarvan niet vergoed via ledenvergoeding</th>
            <th></th>
            <th>Tribunebezorging</th>
            <th>Totale vergoeding</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$kwartaalbijdrages item=data}
        <tr class="{cycle values="odd-row,even-row"}">
            <td>{$data->formattedDate()}</td>
            <td>{$data->basisbedrag|crmMoney:EUR}</td>
            <td>{$data->aantal_leden}</td>
            <td>x {$data->ledenvergoeding_per_lid|crmMoney:EUR}</td>
            <td>{$data->ledenvergoeding|crmMoney:EUR}</td>
            <td>{$data->bezorgde_tribunes}</td>
            <td>{math equation="x-y" x=$data->bezorgde_tribunes y=$data->aantal_leden}</td>
            <td>x {$data->tribunevergoeding_per_tribune|crmMoney:EUR}</td>
            <td>{$data->tribunebezorging_vergoeding|crmMoney:EUR}</td>
            <td><strong>{$data->totaal_bijdrage|crmMoney:EUR}</strong></td>
        </tr>
        {/foreach}
    </tbody>
</table>
