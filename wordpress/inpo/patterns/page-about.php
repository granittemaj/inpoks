<?php
/**
 * Title: INPO: About page
 * Slug: inpo/page-about
 * Categories: inpo
 * Description: INPO's brief: history, mission, vision and values, team and board, strategies and organisational documents. Texts are edited directly on the page.
 * Post Types: page
 * Viewport Width: 1440
 */
?>
<!-- wp:inpo/section-links {"items":"historia | Historia e organizatës\nmisioni | Misioni, vizioni dhe vlerat\nekipi | Ekipi dhe bordi\ndokumentet | Strategjitë dhe dokumentet organizative"} /-->

<!-- wp:group {"tagName":"section","anchor":"historia","className":"wrap sec anchor","layout":{"type":"default"}} -->
<section id="historia" class="wp-block-group wrap sec anchor"><!-- wp:group {"className":"grid12 sec-head","layout":{"type":"default"}} -->
<div class="wp-block-group grid12 sec-head"><!-- wp:paragraph {"className":"kicker"} -->
<p class="kicker">Historia e organizatës</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"h2"} -->
<h2 class="wp-block-heading h2">Nga performancat protestuese te auditimi i tenderëve.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"grid12 about-text","layout":{"type":"default"}} -->
<div class="wp-block-group grid12 about-text"><!-- wp:paragraph {"className":"kicker"} -->
<p class="kicker">Kush jemi</p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"prose","layout":{"type":"default"}} -->
<div class="wp-block-group prose"><!-- wp:paragraph -->
<p>Iniciativa për Progres (INPO) është organizatë joqeveritare dhe jofitimprurëse e themeluar ne vitin 2005 me qëllim që të përparoj pjesëmarrjen e qytetarëve në proceset shoqërore, përmes së cilës pjesëmarrje avokohet për çështje sociale, politike, ekonomike, forcimin e parimit të transparencës dhe llogaridhënies së institucioneve publike, rritjen e kërkesave të qytetarëve si dhe luftimin e korrupsionit, duke synuar në promovimin e vlerave të një shoqërie të hapur dhe demokratike. Vizioni dhe misioni i organizatës përmbushet përmes aktiviteteve të ndryshme të punës të cilat organizohen nga dy (2) programet e specializuara të organizatës: 1. Qeverisja e Mirë dhe 2. Edukimi Demokratik.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>INPO që nga themelimi ka shtyrë përpara agjendat për më shumë transparencë në shpenzimet financiare publike, përfshirjen më të mirë dhe më të gjerë të qytetarëve në proceset e vendimmarrjes publike dhe llogaridhënien e zyrtarëve publik të ndjeshëm ndaj korrupsionit dhe sjelljeve të tjera të këqija në institucionet publike. Organizata është vlerësuar shumë për profesionalizmin e saj dhe qasjen e paanshme dhe ka arritur rezultate të shumta konkrete që nga themelimi.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"timeline","layout":{"type":"default"}} -->
<div class="wp-block-group timeline"><?php
$inpo_timeline = array(
	array( '2005', 'Themelimi në Ferizaj', 'INPO nis me aktivitete që ndihmojnë integrimin e qytetarëve në proceset sociale dhe vendimmarrëse.' ),
	array( '2010', 'Fushata “Zgjohuni”', 'Orë me zile për këshilltarët komunalë të Ferizajt, si kujtesë se janë të zgjedhurit e popullit.' ),
	array( '2019', 'Monitorim në të gjitha komunat', 'Me Institutin e Kosovës për Drejtësi, 160 aktivitete prokurimi të monitoruara në komunat e Kosovës.' ),
	array( '2023', 'Raporti për tenderët e anuluar', 'Publikuar me mbështetjen e Byrosë Ndërkombëtare për Çështje të Narkotikëve dhe Zbatimit të Ligjit (INL).' ),
);
foreach ( $inpo_timeline as $inpo_item ) :
	?><!-- wp:group {"className":"tl","layout":{"type":"default"}} -->
<div class="wp-block-group tl"><!-- wp:paragraph {"className":"yr"} -->
<p class="yr"><?php echo esc_html( $inpo_item[0] ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo esc_html( $inpo_item[1] ); ?></h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php echo esc_html( $inpo_item[2] ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<?php endforeach; ?></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"misioni","className":"wrap sec anchor","layout":{"type":"default"}} -->
<section id="misioni" class="wp-block-group wrap sec anchor"><!-- wp:group {"className":"grid12 sec-head","layout":{"type":"default"}} -->
<div class="wp-block-group grid12 sec-head"><!-- wp:paragraph {"className":"kicker"} -->
<p class="kicker">Misioni, vizioni dhe vlerat</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"h2"} -->
<h2 class="wp-block-heading h2">Pse ekzistojmë dhe si punojmë.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"mv","layout":{"type":"default"}} -->
<div class="wp-block-group mv"><!-- wp:group {"className":"m","layout":{"type":"default"}} -->
<div class="wp-block-group m"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Misioni</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Të promovojmë demokratizimin e qeverisjes lokale përmes aktivizmit qytetar dhe të avancojmë vlerat e një shoqërie të hapur.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"v","layout":{"type":"default"}} -->
<div class="wp-block-group v"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Vizioni</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Institucione lokale që qeverisen me vlera demokratike, ku qytetari është bashkëvendimmarrës.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:heading {"level":3,"className":"sub-h"} -->
<h3 class="wp-block-heading sub-h">Vlerat që promovon organizata</h3>
<!-- /wp:heading -->

<!-- wp:group {"className":"ways","layout":{"type":"default"}} -->
<div class="wp-block-group ways"><!-- wp:group {"className":"way","layout":{"type":"default"}} -->
<div class="wp-block-group way"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Aktivizmi</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>INPO do të nxis aktivizmin qytetar përmes aktivistëve të saj për të krijuar bashkëpunim sa më gjithpërfshirës qytetar për të implementuar misionin e organizatës.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"way","layout":{"type":"default"}} -->
<div class="wp-block-group way"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Profesionalizmi</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>INPO do të mundohet gjithnjë të nxis profesionalizmin dhe aktivitetet e idetë e organizatës do të shtyhen nga profesionalizmi i brendshëm e i jashtëm që shfrytëzohen.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"way","layout":{"type":"default"}} -->
<div class="wp-block-group way"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Integriteti etik dhe moral</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Aktivistët e INPO-së do të jetë individë me integritet etik dhe moral për të përmbushur në mënyrë sa më të denjë misionin dhe vizionin e organizatës.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"way","layout":{"type":"default"}} -->
<div class="wp-block-group way"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Paanshmëria</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>INPO do të nxitet të veproj nga hulumtime, analiza e gjykime objektive duke mënjuar ato subjektive e duke krijuar paanshmëri politike duke mbrojtur gjithnjë interesin e përgjithshëm.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"way","layout":{"type":"default"}} -->
<div class="wp-block-group way"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Respekti</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>INPO do të përmbush e nxit respektin ndërqytetar e ndërinstitucional.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"way","layout":{"type":"default"}} -->
<div class="wp-block-group way"><!-- wp:heading {"level":4} -->
<h4 class="wp-block-heading">Shumëllojshmëria</h4>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>INPO do të jetë promotor i shumëllojshmërisë së komuniteteve të ndryshme dhe komunikimit mes tyre me qëllim eliminimin e diskriminimeve të bazave të ndryshme.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"ekipi","className":"wrap sec anchor","layout":{"type":"default"}} -->
<section id="ekipi" class="wp-block-group wrap sec anchor"><!-- wp:group {"className":"grid12 sec-head","layout":{"type":"default"}} -->
<div class="wp-block-group grid12 sec-head"><!-- wp:paragraph {"className":"kicker"} -->
<p class="kicker">Ekipi dhe bordi</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"h2"} -->
<h2 class="wp-block-heading h2">Njerëzit pas punës sonë.</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:inpo/team {"wrapped":false} /-->
</section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","anchor":"dokumentet","className":"wrap sec anchor","layout":{"type":"default"}} -->
<section id="dokumentet" class="wp-block-group wrap sec anchor"><!-- wp:group {"className":"grid12 sec-head","layout":{"type":"default"}} -->
<div class="wp-block-group grid12 sec-head"><!-- wp:paragraph {"className":"kicker"} -->
<p class="kicker">Dokumentet</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"h2"} -->
<h2 class="wp-block-heading h2">Strategjitë dhe dokumentet organizative</h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"duo","layout":{"type":"default"}} -->
<div class="wp-block-group duo"><!-- wp:group {"className":"box box-pad","layout":{"type":"default"}} -->
<div class="wp-block-group box box-pad"><!-- wp:heading {"level":3,"className":"box-h"} -->
<h3 class="wp-block-heading box-h">Strategjitë dhe dokumentet</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"muted"} -->
<p class="muted">Plani strategjik, statuti dhe dokumentet që rregullojnë punën e organizatës.</p>
<!-- /wp:paragraph -->

<!-- wp:inpo/documents {"layout":"list","categories":"strategjite-dhe-dokumentet-organizative, dokumentet-e-qeverisjes-organizative","wrapped":false} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"box box-pad soft","layout":{"type":"default"}} -->
<div class="wp-block-group box box-pad soft"><!-- wp:heading {"level":3,"className":"box-h"} -->
<h3 class="wp-block-heading box-h">Mundësi punësimi</h3>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"muted"} -->
<p class="muted">Pozitat dhe thirrjet e hapura publikohen këtu.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ink"} -->
<div class="wp-block-button is-style-ink"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/kontakti/' ) ); ?>">Na kontaktoni</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
</section>
<!-- /wp:group -->
