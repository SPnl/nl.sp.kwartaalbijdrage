# nl.sp.kwartaalbijdrage
Functionaliteit voor kwartaalbijdrages aan afdelingen

De kwartaal bijdrages worden in de maand volgend op het kwartaal uitgerekend.

Dit wordt gedaan op basis van het aantal leden en het aantal bezorgde tribunes. 

Op iedere eerste dag van de maand genereert een cron job een activiteit met daarin het aantal leden op dat moment van de afdeling.
Tijdens de kwartaal bijdrage wordt die data gebruikt om te tellen hoeveel leden een afdeling had in het kwartaal. 

**API: kwartaalbijdragen.ledentelling**
Deze extensie komt met een API en een cron job om de leden te tellen.

Je kan eventueel de maand meegeven waarover de telling moet plaatsvinden. Als je die niet meegeeft wordt de huidige maand gebruikt.
