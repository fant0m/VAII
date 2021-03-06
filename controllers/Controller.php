<?php namespace controllers;

use lib\View;
use lib\Validator;
use lib\Auth;

/*abstract*/ class Controller
{
    //abstract function index();

    private function detectDay() {
        $names = array('Nový rok', 'Alexandra', 'Daniela', 'Drahoslav', 'Andera', 'Antónia', 'Bohuslav(a)', 'Severín', 'Alexej', 'Dáša', 'Malvína', 'Ernest', 'Rastislav', 'Radovan', 'Dobroslav', 'Kristína', 'Nataša', 'Bohdana', 'Drahomíra', 'Dalibor', 'Vincent', 'Zora', 'Miloš', 'Timotej', 'Gejza', 'Tamara', 'Bohuš', 'Alfonz', 'Gašpar', 'Ema', 'Emil',
            'Tatiana', 'Erik/Erika', 'Blažej', 'Veronika', 'Agáta', 'Dorota', 'Vanda', 'Zoja', 'Zdenko', 'Gabriela', 'Dezider', 'Perla', 'Arpád', 'Valentín', 'Pravoslav', 'Ida', 'Miloslava', 'Jaromír', 'Vlasta', 'Lívia', 'Eleonóra', 'Etela', 'Roman(a)', 'Metej', 'Frederik/Frederika', 'Viktor', 'Alexander', 'Zlatica',
            'Radomír', 'Albín', 'Anežka', 'Bohumil/Bohumila', 'Kazimír', 'Fridrich', 'Radoslav', 'Tomáš', 'Alan/Alana', 'Františka', 'Branislav, Bruno', 'Angela, Angelika', 'Gregor', 'Vlastimil', 'Matilda', 'Svetlana', 'Boleslav', 'Ľubica', 'Eduard', 'Jozef', 'Víťazoslav', 'Blahoslav', 'Beňadik', 'Adrián', 'Gabriel', 'Marián', 'Emanuel', 'Alena', 'Soňa', 'Miroslav', 'Vieroslava', 'Benjamín',
            'Hugo', 'Zita', 'Richard', 'Izidor', 'Miroslava', 'Irena', 'Zoltán', 'Albert', 'Milena', 'Igor', 'Július', 'Estera', 'Aleš', 'Justína', 'Fedor', 'Dana/Danica', 'Rudolf', 'Valér', 'Jela', 'Marcel', 'Ervín', 'Slavomír', 'Vojtech', 'Juraj', 'Marek', 'Jaroslava', 'Jaroslav', 'Jarmila', 'Lea', 'Anastázia',
            'Sviatok práce', 'Žigmunt', 'Galina', 'Florián', 'Lesana/Lesia', 'Hermína', 'Monika', 'Ingrida', 'Roland', 'Viktória', 'Blažena', 'Pankrác', 'Servác', 'Bonifác', 'Žofia', 'Svetozár', 'Gizela', 'Viola', 'Gertrúda', 'Bernard', 'Zina', 'Júlia, Juliana', 'Želmíra', 'Ela', 'Urban', 'Dušan', 'Iveta', 'Viliam', 'Vilma', 'Ferdinand', 'Petronela/Petrana',
            'Žaneta', 'Xénia', 'Karolína', 'Lenka', 'Laura', 'Norbert', 'Róbert', 'Medard', 'Stanislava', 'Margaréta', 'Dobroslava', 'Zlatko', 'Anton', 'Vasil', 'Vít', 'Blanka', 'Adolf', 'Vratislav/Vratislava', 'Alfréd', 'Valéria', 'Alojz', 'Paulína', 'Sidónia', 'Ján', 'Tadeáš', 'Adriána', 'Ladislav/Ladislava', 'Beáta', 'Peter a Pavol, Petra', 'Melánia',
            'Diana', 'Berta', 'Miloslav', 'Prokop', 'Sviatok sv. Cyrila a Metoda', 'Patrícia, Patrik', 'Oliver', 'Ivan', 'Lujza', 'Amália', 'Milota', 'Nina', 'Margita', 'Kamil', 'Henrich', 'Drahomír', 'Bohuslav', 'Kamila', 'Dušana', 'Iľja/Eliáš', 'Daniel', 'Magdaléna', 'Oľga', 'Vladimír', 'Jakub', 'Anna/Hana', 'Božena', 'Krištof', 'Marta', 'Libuša', 'Ignác',
            'Božidara', 'Gustáv', 'Jerguš', 'Dominik/Dominika', 'Hortenzia', 'Jozefína', 'Štefánia', 'Oskár', 'Ľubomíra', 'Vavrinec', 'Zuzana', 'Darina', 'Ľubomír', 'Mojmír', 'Marcela', 'Leonard', 'Milica', 'Elena, Helena', 'Lýdia', 'Anabela', 'Jana', 'Tichomír', 'Filip', 'Bartolomej', 'Ľudovít', 'Samuel', 'Silvia', 'Augustín', 'Nikola', 'Ružena', 'Nora',
            'Drahoslava', 'Linda', 'Belo', 'Rozália', 'Regína', 'Alica', 'Marianna', 'Miriama', 'Martina', 'Oleg', 'Bystrík', 'Mária', 'Ctibor', 'Ľubomil, Ľudomil', 'Jolana', 'Ľudmila', 'Olympia', 'Eugénia', 'Konštantín', 'Ľuboslav(a)', 'Matúš', 'Móric', 'Zdenka', 'Ľuboš, Ľubor', 'Vladislav', 'Edita', 'Cyprián', 'Václav', 'Michal, Michaela', 'Jarolím',
            'Arnold', 'Levoslav', 'Stela', 'František', 'Viera', 'Natália', 'Eliška', 'Brigita', 'Dionýz', 'Slavomíra', 'Valentína', 'Maximilián', 'Koloman', 'Boris', 'Terézia', 'Vladimíra', 'Hedviga', 'Lukáš', 'Kristián', 'Vendelín', 'Uršuľa', 'Sergej', 'Alojzia', 'Kvetoslava', 'Aurel', 'Demeter', 'Sabína', 'Dobromila, Kevin', 'Klára', 'Šimon/Šimona', 'Aurélia',
            'Denisa/Denis', 'Pamiatka zosnulých', 'Hubert', 'Karol', 'Imrich', 'Renáta', 'René', 'Bohumír', 'Teodor', 'Tibor', 'Martin, Maroš', 'Svätopluk', 'Stanislav', 'Irma', 'Leopold', 'Agnesa', 'Klaudia', 'Eugen', 'Alžbeta', 'Félix', 'Elvíra', 'Cecília', 'Klement', 'Emília', 'Katarína', 'Kornel', 'Milan', 'Henrieta', 'Vratko', 'Ondrej/Andrej',
            'Edmund', 'Bibiána', 'Oldrich', 'Barbora', 'Oto', 'Mikuláš', 'Ambróz', 'Marína', 'Izabela', 'Radúz', 'Hilda', 'Otília', 'Lucia', 'Branislava, Bronislava', 'Ivica', 'Albína', 'Kornélia', 'Sláva/Slávka', 'Judita', 'Dagmara', 'Bohdan', 'Adela', 'Nadežda', 'Adam a Eva', '1. Sviatok vianočný', 'Štefan', 'Filoména', 'Ivana, Ivona', 'Milada', 'Dávid', 'Silvester');

        $day = date("z", time());
        if ((date("Y", time()) % 4 > 0) && ($day > 59)) {
            $day++;
        }

        if ($day == 1 || $day == 121 || $day == 186 || $day == 306 || $day == 359) {
            $name_day = "Dnes je ".$names[$day];
        } else{
            $name_day ="Meniny má ".$names[$day];
        }

        $days = ['Nedeľa', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota'];
        $day = $days[date('w')].', <strong>'.date('d.m.Y').'</strong>';

        return [$day, $name_day];
    }

    public function view($name, $data = array()) {
        $detect = $this->detectDay();
        $data['day'] = $detect[0];
        $data['name_day'] = $detect[1];
        $data['logged'] = Auth::check();
        $data['user'] = Auth::get();
        
        $view = new View($name, $data);

        return $view->render();
    }

    public function validate($data, $rules) {
        $validator = new Validator($data, $rules);

        return $validator->validate();
    }
}