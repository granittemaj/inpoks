/* INPO content data.
   Edit this file to add or change news, publications, programmes and donors.
   Rules: Albanian copy, sentence case, no em dashes, never invent facts.
   Unknown values go in [square brackets]. */
window.INPO_DATA = (function () {
  'use strict';

const NEWS = [
  { id:'pastrimi', date:'18.06.2025', iso:'2025-06-18', cat:'reagim',
    title:'Ankesa e KRM “Pastrimi” përjashtohet pas shqyrtimit. INPO kërkon rishikimin e vendimit të OShP-së',
    short:'Ankesa e KRM “Pastrimi” përjashtohet pas shqyrtimit. INPO kërkon rishikimin e vendimit të OShP-së.',
    excerpt:'INPO shpreh shqetësim për mënyrën se si Organi Shqyrtues i Prokurimit trajtoi ankesën e KRM “Pastrimi” ndaj Komunës së Prishtinës.',
    body:[
      'Iniciativa për Progres (INPO) shpreh shqetësim të thellë për mënyrën se si Organi Shqyrtues i Prokurimit (OShP) ka trajtuar ankesën e Kompanisë Rajonale të Mbeturinave “Pastrimi” ndaj Komunës së Prishtinës.',
      'OShP ka refuzuar të shqyrtojë ankesën duke u thirrur në mungesë juridiksioni, ndonëse paraprakisht e kishte pranuar atë për shqyrtim. Ky qëndrim krijon pasiguri juridike për operatorët ekonomikë dhe dobëson besimin në mekanizmin e ankesave.',
      'INPO kërkon që vendimi të rishikohet dhe që OShP të sqarojë publikisht bazën ligjore mbi të cilën ka ndryshuar qëndrimin për juridiksionin.'
    ], todo:true },
  { id:'mkrs-procesverbal', date:'Maj 2025', iso:'2025-05-20', cat:'reagim',
    title:'Pas reagimit të INPO-s, MKRS publikon procesverbalin e hapjes së tenderit 56 milionësh, por jo vendimin për fituesin',
    excerpt:'Ministria publikoi procesverbalin e hapjes së tenderit për stadiumet vetëm pas reagimit publik. Vendimi për fituesin e kontratës mungon ende.',
    body:['Pas reagimit publik të INPO-s, Ministria e Kulturës, Rinisë dhe Sportit (MKRS) ka publikuar procesverbalin e hapjes së tenderit 56 milionësh për stadiume. Vendimi për fituesin ose fituesit e kontratës ende nuk është publikuar.'], todo:true },
  { id:'mkrs-125', date:'12.05.2025', iso:'2025-05-12', cat:'prokurimi',
    title:'Tenderi 56 milionësh për stadiume i MKRS-së: 125 ditë pa vendim, pa transparencë, pa përgjegjësi',
    excerpt:'Tenderi për ndërtimin dhe rindërtimin e stadiumeve në Ferizaj, Pejë, Prizren dhe Vushtrri mbetet pa vendim 125 ditë pas hapjes.',
    body:['Tenderi i MKRS-së për ndërtimin dhe rindërtimin e stadiumeve në Ferizaj, Pejë, Prizren dhe Vushtrri, me vlerë 56 milionë euro, mbetet pa epilog 125 ditë pas hapjes së tij.','INPO kërkon që ministria të publikojë menjëherë të gjitha dokumentet e procedurës dhe të sqarojë arsyet e vonesës.'], todo:true },
  { id:'prishtina-mbeturina', date:'30.04.2025', iso:'2025-04-30', cat:'reagim',
    title:'INPO ngre shqetësime për mungesë transparence dhe ligjshmërie në prokurimin për menaxhimin e mbeturinave nga Komuna e Prishtinës',
    excerpt:'Iniciativa për Progres (INPO) ka identifikuar një sërë problemesh lidhur me veprimet e Komunës së Prishtinës në fushën e prokurimit për menaxhimin e mbeturinave.',
    body:['Iniciativa për Progres (INPO) ka identifikuar një sërë problemesh lidhur me veprimet e Komunës së Prishtinës në prokurimin për menaxhimin e mbeturinave.'], todo:true },
  { id:'vushtrri', date:'28.04.2025', iso:'2025-04-28', cat:'reagim',
    title:'INPO kërkon anulimin dhe rishikimin e tenderit 4,98 milionë € të Komunës së Vushtrrisë për shkak të kritereve diskriminuese',
    excerpt:'Pas një analize të kujdesshme të dosjes së tenderit të shpallur nga Komuna e Vushtrrisë, INPO kërkon anulimin dhe rishikimin e tij.',
    body:['Iniciativa për Progres (INPO), pas një analize të kujdesshme të dosjes së tenderit të shpallur nga Komuna e Vushtrrisë me vlerë 4,98 milionë euro, ka identifikuar kritere që kufizojnë konkurrencën dhe diskriminojnë operatorët ekonomikë.','INPO kërkon anulimin dhe rishikimin e tenderit.'], todo:true },
  { id:'tm4-2024', date:'24.02.2025', iso:'2025-02-24', cat:'prokurimi',
    title:'Infografikë: kontratat e negociuara pa publikim të njoftimit për kontratë në nivel vendi, tetor-dhjetor 2024',
    excerpt:'INPO ka analizuar trendin e kontratave të nënshkruara përmes procedurës së negociuar pa publikimin e njoftimit për kontratë.',
    body:['INPO ka analizuar trendin e kontratave të nënshkruara përmes procedurës së negociuar pa publikimin e njoftimit për kontratë, në nivel vendi, për tremujorin e katërt të vitit 2024.'], todo:true, pub:'tm4' }
];
const CATS = { reagim:'Reagim publik', prokurimi:'Prokurimi i hapur' };

const PUBS = [
  { id:'oshp', type:'analiza', prog:'qeverisje', short:'OShP', title:'Performanca e OShP-së: efikasiteti i ulët në shqyrtimin e ankesave' },
  { id:'katalizatoret', type:'hulumtime', prog:'qeverisje', short:'KK', title:'Katalizatorët e korrupsionit: praktikat që cënojnë integritetin e prokurimit publik' },
  { id:'integriteti', type:'hulumtime', prog:'edukim', short:'IK', title:'Integriteti i kandidatëve, sundimi i ligjit dhe qeverisja e mirë', partners:'IKD, Lëvizja FOL', donor:'Rockefeller Brothers Fund' },
  { id:'tm4', type:'infografika', prog:'qeverisje', short:'TM4', title:'Kontratat e negociuara pa publikim të njoftimit për kontratë në nivel vendi, tetor-dhjetor 2024' },
  { id:'gjednj', type:'hulumtime', prog:'edukim', short:'GJEDNJ', title:'Aplikimi i jurisprudencës së GJEDNJ-së në Kosovë' },
  { id:'rap', type:'analiza', prog:'qeverisje', short:'RAP', title:'Përfshirja e parimeve të reformës së administratës publike në sektorin e prokurimit' },
  { id:'isa', type:'analiza', prog:'qeverisje', short:'ISA', title:'Udhëzuesi për trajtimin e informatave sekrete afariste në prokurimin publik' },
  { id:'projektet', type:'analiza', prog:'qeverisje', short:'PE', title:'Performanca e komunave në përfshirjen e projekteve ekzekutive në tenderë' },
  { id:'fsk', type:'buletine', prog:'qeverisje', short:'FSK', title:'Sfidat e prokurimit për ushqimin e FSK-së' },
  { id:'ministrite', type:'buletine', prog:'qeverisje', short:'PP', title:'Performanca e ministrive në realizimin e planit të prokurimit për tenderët për punë' },
  { id:'tm3', type:'infografika', prog:'qeverisje', short:'TM3', title:'Kontratat sipas procedurës së negociuar pa publikim të njoftimit, korrik-shtator' },
  { id:'auditimi-2018', type:'inpo', prog:null, short:'2018', title:'Raporti i auditimit: INPO, 2018', year:'2018' }
];
const PTYPES = { analiza:'Analizë', hulumtime:'Hulumtim', infografika:'Infografikë', buletine:'Buletin', inpo:'Raport për INPO' };
const PFILTERS = [['all','Të gjitha'],['analiza','Analiza'],['hulumtime','Hulumtime'],['infografika','Infografika'],['buletine','Buletine'],['inpo','Raporte për INPO']];

const PROGS = {
  qeverisje:{ name:'Qeverisje e mirë', full:'Programi për Qeverisje të Mirë',
    desc:'Promovojmë qeverisjen e përgjegjshme duke nxitur përfshirjen e qytetarëve në vendimmarrje, transparencën, llogaridhënien dhe sundimin e ligjit.',
    ways:[['Monitorim i prokurimit','Ndjekim tenderët e komunave dhe ministrive, nga planifikimi deri te nënshkrimi i kontratës.'],['Reagime publike','Kur gjejmë parregullsi, i bëjmë publike dhe kërkojmë veprim nga institucionet përgjegjëse.'],['Analiza dhe rekomandime','Publikojmë të dhëna, analiza dhe rekomandime për politika më të mira publike.']] },
  edukim:{ name:'Edukim demokratik', full:'Programi për Edukim Demokratik',
    desc:'Krijojmë mundësi që të rinjtë të jenë aktivë dhe promotorë të një shoqërie të hapur.',
    ways:[['Debat','Hapësira ku të rinjtë diskutojnë politika publike dhe vendimet që prekin komunitetin e tyre.'],['Fushata avokuese','Fushata sensibilizuese që i shndërrojnë shqetësimet e qytetarëve në kërkesa konkrete.'],['Aktivizëm qytetar','Nga performancat protestuese te ndërmjetësimi me pushtetin lokal, që nga viti 2005.']] }
};
const DONORS = ['Ambasada e SHBA-së','INL · Departamenti i Shtetit','Rockefeller Brothers Fund','KCSF','USAID','ATRC','YIHR','Balkan Trust for Democracy','DANIDA','Swiss Cooperation Office'];

  return { NEWS, CATS, PUBS, PTYPES, PFILTERS, PROGS, DONORS };
})();
