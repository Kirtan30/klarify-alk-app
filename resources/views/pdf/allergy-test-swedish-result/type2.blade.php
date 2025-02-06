@if($domain === \App\Models\User::KLARIFY_US_STORE)
    <p>You currently have no treatment for your pollen allergy. Which treatment is best for
        you, depends on several different factors, such as the type of symptoms, your general condition,
        the type of treatment you want and the side effects the medicines can cause. Ask the pharmacy staff or take
        contact your GP to discuss which treatment is best for you.</p>
    <p>
        <strong>This test does not replace a doctor's visit. If your symptoms persist, you should make an appointment
            appointment with your doctor.</strong>
    </p>
@else
    <p>Du har for øyeblikket ingen behandling for pollenallergien din. Hvilken behandling som er best for
        deg, avhenger av flere forskjellige faktorer, for eksempel typen symptomer, din generelle tilstand,
        hvilken type behandling du ønsker og bivirkningene medisinene kan gi. Spør apotekpersonalet eller ta
        kontakt med fastlegen din for å diskutere hvilken behandling som er best for deg.</p>
    <p>
        <strong>Denne testen erstatter ikke et legebesøk. Hvis symptomene dine vedvarer, bør du avtale en
            time hos legen din.</strong>
    </p>
@endif
