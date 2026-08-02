<?php

return [

    /*
    | Pravila za pacijentske kanale (sajt, WhatsApp bot, linkovi iz poruka).
    | Recepcija i osoblje uvek mogu da zaobiđu ova pravila.
    */

    'slot_step_minutes' => 15,

    'min_book_hours' => 2,       // najkasnije 2h pre termina (pacijentski kanali)
    'min_cancel_hours' => 24,    // otkazivanje linkom najkasnije 24h pre termina
    'horizon_days' => 90,        // koliko unapred pacijent može da zakaže

    'phone' => '+381 66 123 456',
];
