<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Seeder;

class InstituteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $instituteData = array(
            "Bangladesh Agricultural University", "Bangabandhu Sheikh Mujibur Rahman Agricultural University", "Sher-e-Bangla Agricultural University",
            "Chittagong Veterinary and Animal Sciences University", "Sylhet Agricultural University", "Khulna Agricultural University",
            "Habiganj Agricultural University", "Bangladesh University of Engineering & Technology", "Chittagong University of Engineering & Technology",
            "Rajshahi University of Engineering & Technology", "Khulna University of Engineering & Technology",
            "Dhaka University of Engineering & Technology", "University of Dhaka", "University of Rajshahi",
            "University of Chittagong", "Jahangirnagar University", "Islamic University, Bangladesh", "Khulna University",
            "Jagannath University", "Jatiya Kabi Kazi Nazrul Islam University", "Comilla University", "Begum Rokeya University",
            "Bangladesh University of Professionals", "University of Barisal", "Rabindra University, Bangladesh",
            "ASA University Bangladesh", "Asian University of Bangladesh", "Atish Dipankar University of Science and Technology",
            "Sheikh Hasina University", "Bangabandhu Sheikh Mujibur Rahman University", "Bangabandhu Sheikh Mujib Medical University",
            "Rajshahi Medical University", "Chittagong Medical University", "Sylhet Medical University", "Sheikh Hasina Medical University",
            "American International University - Bangladesh", "Anwer Khan Modern University", "Ahsanullah University of Science and Technology",
            "Bangladesh Army International University of Science and Technology", "Bangladesh Army University of Engineering & Technology (BAUET),Qadirabad",
            "Bangladesh Army University of Science & Technology, Saidpur", "Bangladesh Islami University", "Bangladesh University",
            "Bangladesh University of Business and Technology", "Bangladesh University of Health Sciences", "BGC Trust University Bangladesh",
            "BGMEA University of Fashion & Technology", "BRAC University", "Britannia University", "Canadian University of Bangladesh",
            "CCN University of Science and Technology", "Central Women's University", "Chattogram Independent University", "City University",
            "Cox's Bazar International University", "Daffodil International University", "Darul Ihsan University", "Dhaka International University",
            "East Delta University", "East West University", "Eastern University", "European University of Bangladesh",
            "Exim Bank Agricultural University, Bangladesh", "Fareast International University", "Feni University", "First Capital University of Bangladesh",
            "German University Bangladesh", "Global University Bangladesh", "Gono Bishwabidyalay", "Green University of Bangladesh",
            "Hamdard University Bangladesh", "IBAIS University", "Independent University, Bangladesh", "International Islamic University Chattogram",
            "International Standard University", "International University of Business Agriculture and Technology", "Ishakha International University",
            "Khwaja Yunus Ali University", "Leading University", "Manarat International University", "Metropolitan University",
            "North Bengal International University", "North East University Bangladesh", "North South University", "North Western University",
            "Northern University Bangladesh", "Northern University of Business & Technology, Khulna", "Notre Dame University Bangladesh",
            "NPI University of Bangladesh", "Port City International University", "Premier University", "Presidency University", "Prime University",
            "Primeasia University", "Pundro University of Science and Technology", "Rabindra Maitree University", "Rajshahi Science & Technology University",
            "Ranoda Prashad Shaha University", "Royal University of Dhaka", "Shanto-Mariam University of Creative Technology",
            "Sheikh Fazilatunnesa Mujib University", "Sonargaon University", "Southeast University", "Southern University Bangladesh",
            "Stamford University Bangladesh", "State University of Bangladesh", "Sylhet International University",
            "Tagore University of Creative Arts", "The International University of Scholars", "The Millennium University", "The People's University of Bangladesh",
            "The University of Asia Pacific", "Times University Bangladesh", "United International University", "University of Development Alternative",
            "University of Global Village", "University of Information Technology and Sciences", "University of Liberal Arts Bangladesh",
            "University of Science and Technology Chattogram", "University of South Asia", "Uttara University", "Varendra University",
            "Victoria University of Bangladesh", "World University of Bangladesh", "Z.H. Sikder University of Science & Technology",
            "Stride International School", "LORDS-An English Medium School, Dhaka", "Sydney International School", "Pledge Harbour International School",
            "BAF Shaheen English Medium School", "Yale International School", "Sir John Wilson School", "French International School of Dhaka",
            "Australian International School", "Angelica International School", "Prime Bank English Medium School", "Government Laboratory High School, Mymensingh",
            "Shaheed Ramiz Uddin Cantonment School", "Creative International School", "Tarundia Jagat Memorial High School, Ishwarganj, Mymensingh",
            "Rangon Academy", "Mirpur International Tutorial", "St Gregory's School", "Mohammadpur Preparatory School & College", "Mangrove School",
            "Academia School", "Adroit International School", "Japanese School Dhaka", "St. Peters School of London", "Vision Global School",
            "Regent College, Dhaka", "Queen's School & College", "A. K. High School and College", "Kids Tutorial", "Green Bud School",
            "International Turkish Hope School, Dhaka", "Kakali High School, Dhaka", "IBQ – Institute for British Qualifications", "Morning Glory School (MGS)",
            "British International Kids School (BIKS)", "Averroes International School", "Lakehead Grammar School", "Methodist English Medium School",
            "A. G. Church School Dhaka", "ABC International School", "Alfred International School and College", "Dhamrai Hardinge High School and College",
            "Daffodil International School", "Green Leaf International School (GLIS)", "Mirpur Govt. High School", "Arcadia International School & College (AISCO)",
            "Islami Bank International School & College", "Cardiff International School Dhaka (CISD)", "Barnamala Adarsha High School & College",
            "Bangladesh Web School", "Civil Aviation High School", "Scholastica", "Green Dale International School, Dhaka", "Singapore International School",
            "Canadian Trillinium School", "Begum Sufia Model High School", "Cordova Int'l School & College", "Aga Khan School", "Royal School Dhaka",
            "Ganobhaban Government High School", "M.D.C. Model Institute", "Monipur High School", "Saint Joseph Higher Secondary School",
            "East-West International School & College", "Sristy Central School & College Dhaka", "K. L. Jubilee High School & College",
            "Kallyanpur Girls' School & College", "Nawab Habibullah Model School & College", "Joy Govinda High School, Narayanganj",
            "Gonobidya Niketon High School, Narayanganj", "Morgan Gils High School, Narayanganj", "Narayanganj High School, Narayanganj",
            "Narayanganj Bar Academy", "Cambrian School and College", "Bangladesh International School & College", "Navy Anchorage School and College",
            "Bottomley Home Girls' High School", "Eminence International School & College", "Faizur Rahman Ideal School",
            "S. F. X. Greenherald International School", "Manarat Dhaka International School and College", "Mastermind School", "Maple Leaf International School",
            "Sunnydale School", "Sunbeams School", "PrimRose Kindergarten & School", "Playpen school", "Ebenezer International School",
            "Seabreeze International School", "South Breeze School", "British Primary School Dhaka (BPSD)", "SOS Hermann Gmeiner College", "National Ideal School",
            "Shaheed Police Smrity School & College", "Bir Shrestha Munshi Abdur Rouf Public College", "Viquarunnisa Noon School & College",
            "Cantonment Public School & College", "Mohammadpur Govt. Boys High School, Dhaka", "Mirpur Cantonment Public School & College",
            "DPS STS School Dhaka", "Oxford International School, Dhaka", "American International School of Dhaka", "Life Preparatory School", "K B High School",
            "Armanitola Government High School", "Sharoj International College", "Bashir Uddin Adarsha High School and College", "Baridhara Scholars Institution",
            "Darland International School", "Holy Cross High School", "Willes Little Flower School & College", "Bangladesh International Tutorial",
            "Dhaka Government Muslim High School", "Dhaka International Tutorial", "Dhaka Residential Model College", "Dhanmondi Government Girls' High School",
            "Dhanmondi Tutorial", "European Standard School", "Green Gems International School", "Government Laboratory High School",
            "Gulshan Model High School & College", "Hurdco International School", "Dhanmondi Government Boys' High School", "Ideal School & College",
            "Habirbari Union Sonar Bengali High School", "International School Dhaka", "Park International School and College", "Jamila Aynul High School",
            "Junior Laboratory High School", "Kalyanpur Girls' School & College", "Tejgaon Government High School", "Khilgaon Government High School",
            "Karatitola C.M.S. Memorial High School", "Kurmitola High School", "Motijheel Government Boys' High School", "The Ark Int'l School",
            "Premier School Dhaka", "Ashraf Ali Bahumukhi High School", "Green Scholars International School & College", "The New School Dhaka",
            "Siddheswari Boys' High School", "University Laboratory School and College", "Uttara High School & College", "Don Bosco School and College",
            "Nakhal Para Hossain Ali High School", "New Ananda English School", "Jasim Uddin Institute", "British American English Medium School",
            "Charu Aunggon Art School & Fine Art Academy", "Domrakandi High School", "Hope International School",
            "Fulknuri Kildergarten & high School", "Baliapara High School And College", "British Columbia School", "London School Of English (LSE)",
            "Moulovir Char High School", "Banani Bidyaniketan School and College", "Govt. Science College Att. High School"
        );

        $institutes = array();
        foreach ($instituteData as $data)
        {
            array_push($institutes, array(
                "name"          => $data,
                "created_at"    => now(),
                "updated_at"    => now()
            ));
        }

        Institute::insert($institutes);
    }
}
