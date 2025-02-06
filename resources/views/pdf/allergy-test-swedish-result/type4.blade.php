@if($domain === \App\Models\User::KLARIFY_US_STORE)
    <p>Despite the use of medication, you suffer from the pollen allergy. There are different ways to
        treat pollen allergy on. There are medicines that relieve symptoms such as nasal sprays and
        antihistamine tablets, and there are allergy vaccinations that treat the cause of the allergy. The
        the vast majority can get help with the right medication.
    </p>
    <p>Which treatment is best for you depends on several different factors, for example
        type of symptoms, your general condition, the type of treatment you want and the side effects
        the medicines can give.
    </p>
    <p>At your next doctor's visit, you should discuss which treatment options are best for you.</p>
    <p>Feel free to print out this allergy test and show it to your doctor.</p>
    <p>
        <strong>
            This test does not replace a doctor's visit. If your symptoms persist, you should make an appointment
            with your doctor.
        </strong>
    </p>
    <h3>Your VAS score:{{data_get($result, 'vas_score')}}</h3>
    <p>The VAS score tells you how much discomfort you experience from your allergy even if you use medication</p>
    <p><strong>0-2 points:</strong> Your troubles are not so great. You can continue with your current one
        treatment.</p>
    <p><strong>2-5 points:</strong> You experience mild to moderate discomfort. Contact your doctor and together
        can you find a way to optimize your current treatment.</p>
    <p><strong>5+ points:</strong> Despite the use of medication, you seem to have major problems,
        which certainly affects your quality of life during the pollen season. Contact your GP for further information
        investigation. Allergy vaccination may be the next step in your treatment.</p>
    <p style="font-size: 8px;">
        Reference: Bousguet, P. J et al. Allergic rhinitis. Nature reviews, 2020; 6:95
    </p>
@else
    <p>Til tross for bruk av medisiner, plages du av pollenallergien. Det finnes forskjellige måter å
        behandle pollenallergi på. Det er medisiner som lindrer symptomer slik som nesespray og
        antihistamintabletter, og det finnes allergivaksinasjon som behandler årsaken til allergien. De
        aller fleste kan få hjelp med riktig medisinering.</p>
    <p>Hvilken behandling som passer best for deg, avhenger av flere forskjellige faktorer, for eksempel
        type symptomer, din generelle tilstand, hvilken type behandling du ønsker og bivirkningene
        medisinene kan gi.</p>
    <p>Ved ditt neste legebesøk, bør du diskutere hvilke behandlingsalternativer som er best for deg.</p>
    <p>Skriv gjerne ut denne allergitesten og vis den til legen din.</p>
    <p>
        <strong>
            Denne testen erstatter ikke et legebesøk. Hvis symptomene dine vedvarer, bør du avtale en time
            hos legen din.
        </strong>
    </p>
    <h3>Din VAS-poengsum:{{data_get($result, 'vas_score')}}</h3>
    <p>VAS scoren forteller deg hvor mye ubehag du opplever av allergien din selv om du bruker medisiner</p>
    <p><strong>0-2 poeng:</strong> Dine plager er ikke så store. Du kan fortsette med din nåværende
        behandling.</p>
    <p><strong>2-5 poeng:</strong> Du opplever mild til moderat ubehag. Ta kontakt med din lege og sammen
        kan dere finne en måte å optimalisere din nåværende behandling.</p>
    <p><strong>5+ poeng:</strong> Til tross for bruk av medisiner ser det ut til at du har store problemer,
        noe som sikkert påvirker livskvaliteten din i pollensesongen. Kontakt din fastlege for videre
        utredning. Allergivaksinasjon kan være neste skritt i din behandling.</p>
    <p style="font-size: 8px;">
        Referanse:Bousguet, P. J et al. Allergic rhinitis. Nature reviews, 2020; 6:95
    </p>
@endif
