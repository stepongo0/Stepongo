const stateDistricts = {
    "Andhra Pradesh": [
        "Anantapur", "Chittoor", "East Godavari", "Guntur", "Kadapa", "Krishna", "Kurnool", "Nellore",
        "Prakasam", "Srikakulam", "Visakhapatnam", "Vizianagaram", "West Godavari", "Tirupati", "Nandyal"
    ],
    "Arunachal Pradesh": [
        "Tawang", "West Kameng", "East Kameng", "Papum Pare", "Kurung Kumey", "Kra Daadi", "Lower Subansiri",
        "Upper Subansiri", "West Siang", "East Siang", "Upper Siang", "Lower Siang", "Dibang Valley",
        "Lower Dibang Valley", "Anjaw"
    ],
    "Assam": [
        "Baksa", "Barpeta", "Biswanath", "Bongaigaon", "Cachar", "Charaideo", "Chirang", "Darrang", "Dhemaji",
        "Dhubri", "Dibrugarh", "Goalpara", "Golagadh", "Hailakandi", "Jorhat"
    ],
    "Bihar": [
        "Araria", "Arwal", "Aurangabad", "Banka", "Begusarai", "Bhagalpur", "Bhojpur", "Buxar", "Darbhanga",
        "East Champaran", "Gaya", "Gopalganj", "Jamui", "Jehanabad", "Kaimur"
    ],
    "Chhattisgarh": [
        "Balod", "Baloda Bazar", "Balrampur", "Bastar", "Bemetara", "Bijapur", "Bilaspur", "Dantewada",
        "Dhamtari", "Durg", "Gariaband", "Janjgir-Champa", "Jashpur", "Kabirdham", "Kanker"
    ],
    "Goa": [
        "North Goa", "South Goa"
    ],
    "Gujarat": [
        "Ahmedabad", "Amreli", "Anand", "Aravalli", "Banaskantha", "Bharuch", "Bhavnagar", "Botad",
        "Chhota Udaipur", "Dahod", "Dang", "Devbhoomi Dwarka", "Gandhinagar", "Gir Somnath", "Jamnagar"
    ],
    "Haryana": [
        "Ambala", "Bhiwani", "Charkhi Dadri", "Faridabad", "Fatehabad", "Gurgaon", "Hisar", "Jhajjar", "Jind",
        "Kaithal", "Karnal", "Kurukshetra", "Mahendragarh", "Nuh", "Palwal"
    ],
    "Himachal Pradesh": [
        "Bilaspur", "Chamba", "Hamirpur", "Kangra", "Kinnaur", "Kullu", "Lahaul and Spiti", "Mandi", "Shimla",
        "Sirmaur", "Solan", "Una", "Palampur", "Sundernagar", "Nahan"
    ],
    "Jharkhand": [
        "Bokaro", "Chatra", "Deoghar", "Dhanbad", "Dumka", "East Singhbhum", "Garhwa", "Giridih", "Godda",
        "Gumla", "Hazaribagh", "Jamtara", "Khunti", "Koderma", "Latehar"
    ],
    "Karnataka": [
        "Bagalkot", "Ballari", "Belagavi", "Bengaluru Rural", "Bengaluru Urban", "Bidar", "Chamarajanagar",
        "Chikballapur", "Chikkamagaluru", "Chitradurga", "Dakshina Kannada", "Davanagere", "Dharwad", "Gadag",
        "Hassan"
    ],
    "Kerala": [
        "Alappuzha", "Ernakulam", "Idukki", "Kannur", "Kasaragod", "Kollam", "Kottayam", "Kozhikode",
        "Malappuram", "Palakkad", "Pathanamthitta", "Thiruvananthapuram", "Thrissur", "Wayanad", "Muvattupuzha"
    ],
    "Madhya Pradesh": [
        "Agar Malwa", "Alirajpur", "Anuppur", "Ashoknagar", "Balaghat", "Barwani", "Betul", "Bhind", "Bhopal",
        "Burhanpur", "Chhatarpur", "Chhindwara", "Damoh", "Datia", "Dewas"
    ],
    "Maharashtra": [
        "Ahmednagar", "Akola", "Amravati", "Aurangabad", "Beed", "Bhandara", "Buldhana", "Chandrapur", "Dhule",
        "Gadchiroli", "Gondia", "Hingoli", "Jalgaon", "Jalna", "Kolhapur"
    ],
    "Manipur": [
        "Bishnupur", "Chandel", "Churachandpur", "Imphal East", "Imphal West", "Jiribam", "Kakching", "Kamjong",
        "Kangpokpi", "Noney", "Pherzawl", "Senapati", "Tamenglong", "Tengnoupal", "Thoubal"
    ],
    "Meghalaya": [
        "East Garo Hills", "East Jaintia Hills", "East Khasi Hills", "North Garo Hills", "Ri-Bhoi",
        "South Garo Hills", "South West Garo Hills", "South West Khasi Hills", "West Garo Hills",
        "West Jaintia Hills", "West Khasi Hills", "Mairang", "Resubelpara", "Baghmara", "Williamnagar"
    ],
    "Mizoram": [
        "Aizawl", "Champhai", "Kolasib", "Lawngtlai", "Lunglei", "Mamit", "Saiha", "Serchhip", "Hnahthial",
        "Khawzawl", "Saitual", "North Vanlaiphai", "Biate", "Zawlnuam", "Thenzawl"
    ],
    "Nagaland": [
        "Dimapur", "Kiphire", "Kohima", "Longleng", "Mokokchung", "Mon", "Peren", "Phek", "Tuensang", "Wokha",
        "Zunheboto", "Tseminyu", "Shamator", "Noklak", "Chümoukedima"
    ],
    "Odisha": [
        "Angul", "Balangir", "Balasore", "Bargarh", "Bhadrak", "Boudh", "Cuttack", "Deogarh", "Dhenkanal",
        "Gajapati", "Ganjam", "Jagatsinghpur", "Jajpur", "Jharsuguda", "Kalahandi"
    ],
    "Punjab": [
        "Amritsar", "Barnala", "Bathinda", "Faridkot", "Fatehgarh Sahib", "Fazilka", "Ferozepur", "Gurdaspur",
        "Hoshiarpur", "Jalandhar", "Kapurthala", "Ludhiana", "Mansa", "Moga", "Mohali"
    ],
    "Rajasthan": [
        "Ajmer", "Alwar", "Banswara", "Baran", "Barmer", "Bharatpur", "Bhilwara", "Bikaner", "Bundi",
        "Chittorgarh", "Churu", "Dausa", "Dholpur", "Dungarpur", "Hanumangarh"
    ],
    "Sikkim": [
        "East Sikkim", "West Sikkim", "North Sikkim", "South Sikkim", "Gyalshing", "Namchi", "Mangan", "Pakyong",
        "Soreng", "Ravangla", "Yuksom", "Dzongu", "Rinchenpong", "Singtam", "Tadong"
    ],
    "Tamil Nadu": [
        "Ariyalur", "Chengalpattu", "Chennai", "Coimbatore", "Cuddalore", "Dharmapuri", "Dindigul", "Erode",
        "Kallakurichi", "Kanchipuram", "Kanyakumari", "Karur", "Krishnagiri", "Madurai", "Nagapattinam"
    ],
    "Telangana": [
        "Adilabad", "Bhadradri Kothagudem", "Hyderabad", "Jagtial", "Jangaon", "Jayashankar Bhupalpally",
        "Jogulamba Gadwal", "Kamareddy", "Karimnagar", "Khammam", "Komaram Bheem", "Mahabubabad",
        "Mahabubnagar", "Mancherial", "Medak"
    ],
    "Tripura": [
        "Dhalai", "Gomati", "Khowai", "North Tripura", "Sepahijala", "South Tripura", "Unakoti", "West Tripura",
        "Ambassa", "Belonia", "Dharmanagar", "Kailasahar", "Kamalpur", "Kanchanpur", "Udaipur"
    ],
    "Uttar Pradesh": [
        "Agra", "Aligarh", "Allahabad", "Ambedkar Nagar", "Amethi", "Amroha", "Auraiya", "Azamgarh", "Baghpat",
        "Bahraich", "Ballia", "Balrampur", "Banda", "Barabanki", "Bareilly"
    ],
    "Uttarakhand": [
        "Almora", "Bageshwar", "Chamoli", "Champawat", "Dehradun", "Haridwar", "Nainital", "Pauri Garhwal",
        "Pithoragarh", "Rudraprayag", "Tehri Garhwal", "Udham Singh Nagar", "Uttarkashi", "Kashipur", "Roorkee"
    ],
    "West Bengal": [
        "Alipurduar", "Bankura", "Birbhum", "Cooch Behar", "Dakshin Dinajpur", "Darjeeling", "Hooghly", "Howrah",
        "Jalpaiguri", "Jhargram", "Kalimpong", "Kolkata", "Malda", "Murshidabad", "Nadia", "North 24 Parganas",
        "Paschim Bardhaman", "Paschim Medinipur", "Purba Bardhaman", "Purba Medinipur", "Purulia",
        "South 24 Parganas", "Uttar Dinajpur"
    ]
};

const preferredLocations = [
    "Mumbai", "Delhi", "Kolkata", "Chennai", "Bangalore", "Hyderabad", "Pune", "Ahmedabad", "Surat", "Jaipur",
    "Lucknow", "Kanpur", "Nagpur", "Indore", "Bhopal", "Patna", "Vadodara", "Ghaziabad", "Ludhiana", "Agra",
    "Nashik", "Faridabad", "Meerut", "Rajkot", "Varanasi", "Srinagar", "Aurangabad", "Dhanbad", "Amritsar",
    "Allahabad", "Ranchi", "Gwalior", "Jabalpur", "Coimbatore", "Vijayawada", "Madurai", "Visakhapatnam",
    "Chandigarh", "Guwahati", "Hubli-Dharwad", "Thiruvananthapuram", "Kochi", "Cuttack", "Bhubaneswar",
    "Dehradun", "Mysore", "Jodhpur", "Raipur", "Kolhapur", "Solapur", "Other"
];

const banks = [
    "State Bank of India", "HDFC Bank", "ICICI Bank", "Axis Bank", "Punjab National Bank",
    "Bank of Baroda", "Canara Bank", "Union Bank of India", "Bank of India", "Indian Bank",
    "Central Bank of India", "Indian Overseas Bank", "UCO Bank", "Bank of Maharashtra",
    "Punjab & Sind Bank", "IDBI Bank", "Federal Bank", "Kotak Mahindra Bank", "Yes Bank",
    "IndusInd Bank", "RBL Bank", "CSB Bank", "Dhanlaxmi Bank", "City Union Bank",
    "South Indian Bank", "Tamilnad Mercantile Bank", "Karnataka Bank", "Jammu & Kashmir Bank",
    "IDFC FIRST Bank", "AU Small Finance Bank", "Bandhan Bank", "Other"
];

const MAX_FILE_SIZE_MB = 10;
const MAX_FILE_SIZE_BYTES = MAX_FILE_SIZE_MB * 1024 * 1024;

// --- Confirmation Page Elements ---
const registrationFormSection = document.getElementById('registration-form-section');
const confirmationPageSection = document.getElementById('confirmation-page-section');

// Function to display error messages
function displayErrorMessage(elementId, message) {
    const errorElement = document.getElementById(elementId);
    const inputElement = document.getElementById(elementId.replace('Error', ''));
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    if (inputElement) {
        inputElement.classList.add('input-error');
    }
}

// Function to clear error messages
function clearErrorMessage(elementId) {
    const errorElement = document.getElementById(elementId);
    const inputElement = document.getElementById(elementId.replace('Error', ''));
    if (errorElement) {
        errorElement.style.display = 'none';
    }
    if (inputElement) {
        inputElement.classList.remove('input-error');
    }
}

// Function to validate required fields
function validateRequired(inputElement, errorElementId) {
    // Check if inputElement exists before accessing its properties
    if (!inputElement) {
        console.error("Input element not found for ID:", errorElementId.replace('Error', ''));
        return false;
    }

    if (inputElement.value.trim() === '' || inputElement.value === 'Select State' || inputElement.value === 'Select District' || inputElement.value === 'Select your gender' || inputElement.value === 'Select Skill' || inputElement.value === 'Select ID' || inputElement.value === 'Select Experience') {
        const labelElement = inputElement.previousElementSibling || (inputElement.labels && inputElement.labels[0]);
        const labelText = labelElement ? labelElement.textContent : '';
        displayErrorMessage(errorElementId, labelText.replace(' *', '') + ' is required.');
        return false;
    }
    clearErrorMessage(errorElementId);
    return true;
}

const stateSelect = document.getElementById("state");
const districtSelect = document.getElementById("district");

// Event listener for state change
stateSelect.addEventListener("change", () => {
    const selectedState = stateSelect.value;
    // console.log("Selected State:", selectedState); // Debugging line

    const districts = stateDistricts[selectedState] || [];
    // console.log("Districts for selected state:", districts); // Debugging line

    districtSelect.innerHTML = '<option value="">Select District</option>'; // Reset districts
    districts.forEach(district => {
        const option = document.createElement("option");
        option.value = district;
        option.textContent = district;
        districtSelect.appendChild(option);
    });
    validateRequired(stateSelect, 'stateError'); // Validate state immediately
    if (!selectedState) { // If state is cleared, clear district and its error too
        districtSelect.value = '';
        clearErrorMessage('districtError');
    }
});
districtSelect.addEventListener("change", () => validateRequired(districtSelect, 'districtError'));


const skillsDropdown = document.getElementById('skills');
const otherSkillInput = document.getElementById('otherSkill');
skillsDropdown.addEventListener('change', () => {
    otherSkillInput.style.display = (skillsDropdown.value === 'Other') ? 'block' : 'none';
    if (skillsDropdown.value !== 'Other') {
        otherSkillInput.value = '';
        clearErrorMessage('skillsError'); // Clear error if 'Other' is deselected
    }
    validateRequired(skillsDropdown, 'skillsError');
});

const govtIdTypeSelect = document.getElementById('govtIdType');
const govtIdNumberInput = document.getElementById('govtIdNumber');

const govtIdRules = {
    'Aadhaar Card': { pattern: /^\d{12}$/, message: 'Aadhaar number must be 12 digits long.' },
    'PAN Card': { pattern: /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/, message: 'PAN number must be 10 alphanumeric characters (e.g., ABCDE1234F).' },
    'Voter Card': { pattern: /^[A-Z]{3}[0-9]{7}$/, message: 'Voter ID number should be 10 alphanumeric characters (e.g., ABC1234567).' },
    'Driving Licence': { pattern: /^[A-Z]{2}\d{2}[A-Z]{2}\d{4}[0-9]{7}$|^[A-Z]{2}\d{2}\d{11}$/, message: 'Driving Licence number should be 15 alphanumeric characters.' },
    'Passport ID': { pattern: /^[A-PR-WY][1-9]\d\d\d[1-9]\d{6}$/, message: 'Passport ID must be 8 characters long (e.g., A1234567).' }
};

// Function to validate Govt ID
function validateGovtId() {
    const idType = govtIdTypeSelect.value;
    const idNumber = govtIdNumberInput.value.trim();
    let isValid = true;

    // Clear all specific ID errors first
    clearErrorMessage('govtIdNumberError');
    clearErrorMessage('aadhaarError');
    clearErrorMessage('panError');
    clearErrorMessage('voterError');
    clearErrorMessage('drivingLicenseError');
    clearErrorMessage('passportError');
    clearErrorMessage('govtIdTypeError'); // Clear ID Type error here too

    if (!idType) {
        displayErrorMessage('govtIdTypeError', 'Govt ID Type is required.');
        isValid = false;
    }

    if (!idNumber) {
        displayErrorMessage('govtIdNumberError', 'Govt ID Number is required.');
        isValid = false;
    } else if (govtIdRules[idType] && !govtIdRules[idType].pattern.test(idNumber)) {
        const specificErrorId = idType.replace(/\s/g, '').replace('Card', '').toLowerCase() + 'Error';
        displayErrorMessage(specificErrorId, govtIdRules[idType].message);
        isValid = false;
    }
    
    // Apply error class to input itself based on overall validation
    govtIdNumberInput.classList.toggle('input-error', !isValid && idNumber !== ''); // Only add red if number is not empty and invalid
    govtIdTypeSelect.classList.toggle('input-error', !idType); // Add red if ID type is not selected

    return isValid;
}

govtIdTypeSelect.addEventListener('change', validateGovtId);
govtIdNumberInput.addEventListener('input', validateGovtId);

const pincodeInput = document.getElementById('pincode');
function validatePincode() {
    const pincode = pincodeInput.value.trim();
    if (pincode.length !== 6 || !/^\d+$/.test(pincode)) {
        displayErrorMessage('pincodeError', 'Pincode must be 6 digits long.');
        return false;
    }
    clearErrorMessage('pincodeError');
    return true;
}
pincodeInput.addEventListener('input', validatePincode);

const contactNumberInput = document.getElementById('contactNumber');
const alternativeContactNumberInput = document.getElementById('alternativeContactNumber');

function validateContactNumber(inputElement, errorElementId) {
    const number = inputElement.value.trim();
    if (number.length !== 10 || !/^\d+$/.test(number)) {
        const labelElement = inputElement.labels && inputElement.labels[0];
        const labelText = labelElement ? labelElement.textContent : '';
        displayErrorMessage(errorElementId, labelText.replace(' *', '') + ' must be 10 digits long.');
        return false;
    }
    clearErrorMessage(errorElementId);
    return true;
}
contactNumberInput.addEventListener('input', () => validateContactNumber(contactNumberInput, 'contactNumberError'));
alternativeContactNumberInput.addEventListener('input', () => {
    if (alternativeContactNumberInput.value.trim() !== '') {
        validateContactNumber(alternativeContactNumberInput, 'alternativeContactNumberError');
    } else {
        clearErrorMessage('alternativeContactNumberError');
    }
});

const govtIdPhotoInput = document.getElementById('govtIdPhoto');
const selfPhotoInput = document.getElementById('selfPhoto');
const passbookPhotoInput = document.getElementById('passbookPhoto');

function validateFileSizeAndType(fileInput, errorElementId) {
    if (fileInput.files.length === 0) {
        displayErrorMessage(errorElementId, (fileInput.labels && fileInput.labels[0] ? fileInput.labels[0].textContent.replace(' (PDF, max 10 MB)', '').trim() : 'File') + ' is required.');
        return false;
    }
    const file = fileInput.files[0];
    if (file.type !== 'application/pdf') {
        displayErrorMessage(errorElementId, 'File must be a PDF.');
        return false;
    }
    if (file.size > MAX_FILE_SIZE_BYTES) {
        displayErrorMessage(errorElementId, `File size exceeds ${MAX_FILE_SIZE_MB} MB.`);
        return false;
    }
    clearErrorMessage(errorElementId);
    return true;
}

govtIdPhotoInput.addEventListener('change', () => validateFileSizeAndType(govtIdPhotoInput, 'govtIdPhotoError'));
selfPhotoInput.addEventListener('change', () => validateFileSizeAndType(selfPhotoInput, 'selfPhotoError'));
passbookPhotoInput.addEventListener('change', () => validateFileSizeAndType(passbookPhotoInput, 'passbookPhotoError'));

const accountNumberInput = document.getElementById('accountNumber');
const confirmAccountNumberInput = document.getElementById('confirmAccountNumber');

function validateAccountNumbers() {
    const accNum = accountNumberInput.value.trim();
    const confirmAccNum = confirmAccountNumberInput.value.trim();

    if (!accNum) {
        displayErrorMessage('accountNumberError', 'Account Number is required.');
        return false;
    } else {
        clearErrorMessage('accountNumberError');
    }

    if (!confirmAccNum) {
        displayErrorMessage('confirmAccountNumberError', 'Confirm Account Number is required.');
        return false;
    } else if (accNum !== confirmAccNum) {
        displayErrorMessage('confirmAccountNumberError', 'Account numbers do not match.');
        return false;
    }
    clearErrorMessage('confirmAccountNumberError');
    return true;
}
accountNumberInput.addEventListener('input', validateAccountNumbers);
confirmAccountNumberInput.addEventListener('input', validateAccountNumbers);

const ifscCodeInput = document.getElementById('ifscCode');
function validateIFSCCode() {
    const ifsc = ifscCodeInput.value.trim().toUpperCase();
    if (!/^[A-Z]{4}0[A-Z0-9]{6}$/.test(ifsc)) {
        displayErrorMessage('ifscCodeError', 'IFSC Code must be 11 alphanumeric characters (e.g., SBIN0000123).');
        return false;
    }
    clearErrorMessage('ifscCodeError');
    return true;
}
ifscCodeInput.addEventListener('input', validateIFSCCode);

const preferredLocationDisplay = document.getElementById('preferredLocationDisplay');
const preferredLocationDropdown = document.getElementById('preferredLocationDropdown');
const selectedLocationsTags = document.getElementById('selectedLocationsTags');
const selectedCountSpan = document.getElementById('selectedCount');
const otherLocationInput = document.getElementById('otherLocation');
// Create locationSearchInput dynamically to ensure it's always available for prepending
const locationSearchInput = document.createElement('input');
locationSearchInput.type = 'text';
locationSearchInput.id = 'locationSearch';
locationSearchInput.placeholder = 'Search locations...';
locationSearchInput.setAttribute('aria-label', 'Search Preferred Job Locations');


let selectedLocations = new Set();

function updateSelectedLocationCount() {
    const count = selectedLocations.size;
    selectedCountSpan.textContent = `${count} selected`;
    preferredLocationDisplay.classList.toggle('active', count > 0);
    if (count === 0) {
        displayErrorMessage('preferredLocationError', 'At least one preferred job location is required.');
    } else if (selectedLocations.has('Other') && otherLocationInput.value.trim() === '') {
         // Do nothing here, validation for 'Other' specifically handled during form submission
    }
    else {
        clearErrorMessage('preferredLocationError');
    }
}

function renderLocationCheckboxes(locationsToRender = preferredLocations) {
    const currentSearchValue = locationSearchInput.value;
    preferredLocationDropdown.innerHTML = '';
    preferredLocationDropdown.appendChild(locationSearchInput); // Prepend the search input
    locationSearchInput.value = currentSearchValue; // Restore search value

    locationsToRender.forEach(location => {
        const label = document.createElement('label');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = location;
        checkbox.checked = selectedLocations.has(location);
        checkbox.setAttribute('role', 'option');

        checkbox.addEventListener('change', (event) => {
            if (event.target.checked) {
                selectedLocations.add(location);
                if (location === 'Other') {
                    otherLocationInput.style.display = 'block';
                    otherLocationInput.focus();
                }
            } else {
                selectedLocations.delete(location);
                if (location === 'Other') {
                    otherLocationInput.style.display = 'none';
                    otherLocationInput.value = '';
                    clearErrorMessage('preferredLocationError'); // Clear specific error if 'Other' is deselected
                }
            }
            renderSelectedLocationTags();
            updateSelectedLocationCount();
        });
        label.appendChild(checkbox);
        label.appendChild(document.createTextNode(location));
        preferredLocationDropdown.appendChild(label);
    });
}

function renderSelectedLocationTags() {
    selectedLocationsTags.innerHTML = '';
    selectedLocations.forEach(location => {
        const tag = document.createElement('span');
        tag.classList.add('selected-location-tag');
        tag.textContent = location;

        const removeBtn = document.createElement('span');
        removeBtn.classList.add('tag-remove');
        removeBtn.innerHTML = '&times;';
        removeBtn.onclick = () => {
            selectedLocations.delete(location);
            const checkbox = preferredLocationDropdown.querySelector(`input[value="${location}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
            if (location === 'Other') {
                otherLocationInput.style.display = 'none';
                otherLocationInput.value = '';
                clearErrorMessage('preferredLocationError'); // Clear error if 'Other' tag is removed
            }
            renderSelectedLocationTags();
            updateSelectedLocationCount();
        };
        tag.appendChild(removeBtn);
        selectedLocationsTags.appendChild(tag);
    });
}

preferredLocationDisplay.addEventListener('click', () => {
    const isShowing = preferredLocationDropdown.classList.toggle('show');
    preferredLocationDisplay.classList.toggle('show', isShowing);
    preferredLocationDisplay.setAttribute('aria-expanded', isShowing);
    if (isShowing) {
        locationSearchInput.focus();
        renderLocationCheckboxes(); // Re-render to ensure current selections/search are fresh
    }
});

document.addEventListener('click', (event) => {
    if (!preferredLocationDisplay.contains(event.target) && !preferredLocationDropdown.contains(event.target)) {
        preferredLocationDropdown.classList.remove('show');
        preferredLocationDisplay.classList.remove('show');
        preferredLocationDisplay.setAttribute('aria-expanded', 'false');
    }
});

locationSearchInput.addEventListener('input', () => {
    const searchText = locationSearchInput.value.toLowerCase();
    const filtered = preferredLocations.filter(location => location.toLowerCase().includes(searchText));
    renderLocationCheckboxes(filtered);
});

renderLocationCheckboxes(); // Initial render
renderSelectedLocationTags(); // Initial render
updateSelectedLocationCount(); // Initial update


const bankNameDisplay = document.getElementById('bankNameDisplay');
const bankNameDropdown = document.getElementById('bankNameDropdown');
const selectedBankNameSpan = document.getElementById('selectedBankName');
const otherBankNameInput = document.getElementById('otherBankName');
// Create bankSearchInput dynamically
const bankSearchInput = document.createElement('input');
bankSearchInput.type = 'text';
bankSearchInput.id = 'bankSearch';
bankSearchInput.placeholder = 'Search banks...';
bankSearchInput.setAttribute('aria-label', 'Search Bank Name');


let selectedBank = '';

function updateSelectedBankDisplay() {
    if (selectedBank) {
        selectedBankNameSpan.textContent = selectedBank;
        bankNameDisplay.classList.add('active');
        clearErrorMessage('bankNameError'); // Clear error once a bank is selected
    } else {
        selectedBankNameSpan.textContent = 'Select Bank';
        bankNameDisplay.classList.remove('active');
        displayErrorMessage('bankNameError', 'Bank Name is required.');
    }
}

function renderBankCheckboxes(banksToRender = banks) {
    const currentSearchValue = bankSearchInput.value;
    bankNameDropdown.innerHTML = '';
    bankNameDropdown.appendChild(bankSearchInput); // Prepend the search input
    bankSearchInput.value = currentSearchValue; // Restore search value

    banksToRender.forEach(bank => {
        const label = document.createElement('label');
        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = 'bankSelection'; // Ensures only one can be selected
        radio.value = bank;
        radio.checked = (selectedBank === bank);
        radio.setAttribute('role', 'option');

        radio.addEventListener('change', (event) => {
            selectedBank = event.target.value;
            updateSelectedBankDisplay();
            if (selectedBank === 'Other') {
                otherBankNameInput.style.display = 'block';
                otherBankNameInput.focus();
            } else {
                otherBankNameInput.style.display = 'none';
                otherBankNameInput.value = '';
            }
            bankNameDropdown.classList.remove('show'); // Close dropdown after selection
            bankNameDisplay.classList.remove('show');
            bankNameDisplay.setAttribute('aria-expanded', 'false');
            clearErrorMessage('bankNameError'); // Clear specific error here
        });

        label.appendChild(radio);
        label.appendChild(document.createTextNode(bank));
        bankNameDropdown.appendChild(label);
    });
}

bankNameDisplay.addEventListener('click', () => {
    const isShowing = bankNameDropdown.classList.toggle('show');
    bankNameDisplay.classList.toggle('show', isShowing);
    bankNameDisplay.setAttribute('aria-expanded', isShowing);
    if (isShowing) {
        bankSearchInput.focus();
        renderBankCheckboxes(); // Re-render to ensure current selection/search are fresh
    }
});

document.addEventListener('click', (event) => {
    if (!bankNameDisplay.contains(event.target) && !bankNameDropdown.contains(event.target)) {
        bankNameDropdown.classList.remove('show');
        bankNameDisplay.classList.remove('show');
        bankNameDisplay.setAttribute('aria-expanded', 'false');
    }
});

bankSearchInput.addEventListener('input', () => {
    const searchText = bankSearchInput.value.toLowerCase();
    const filtered = banks.filter(bank => bank.toLowerCase().includes(searchText));
    renderBankCheckboxes(filtered);
});

renderBankCheckboxes(); // Initial render
updateSelectedBankDisplay(); // Initial update

// Attach general validation listeners
document.getElementById('fullName').addEventListener('input', (e) => validateRequired(e.target, 'fullNameError'));
document.getElementById('address').addEventListener('input', (e) => validateRequired(e.target, 'addressError'));
document.getElementById('gender').addEventListener('change', (e) => validateRequired(e.target, 'genderError'));
document.getElementById('dob').addEventListener('change', (e) => validateRequired(e.target, 'dobError'));
document.getElementById('experience').addEventListener('change', (e) => validateRequired(e.target, 'experienceError'));
document.getElementById('accountHolderName').addEventListener('input', (e) => validateRequired(e.target, 'accountHolderNameError'));
document.getElementById('branchName').addEventListener('input', (e) => validateRequired(e.target, 'branchNameError'));


document.getElementById('saveProfileBtn').addEventListener('click', (event) => {
    event.preventDefault(); // Prevent default form submission

    let isFormValid = true; // Flag to track overall form validity

    // --- Validation Calls ---
    // Group required field validations
    isFormValid = validateRequired(document.getElementById('fullName'), 'fullNameError') && isFormValid;
    isFormValid = validateRequired(document.getElementById('address'), 'addressError') && isFormValid;
    isFormValid = validatePincode() && isFormValid;
    isFormValid = validateRequired(stateSelect, 'stateError') && isFormValid;
    isFormValid = validateRequired(districtSelect, 'districtError') && isFormValid;
    isFormValid = validateRequired(document.getElementById('gender'), 'genderError') && isFormValid;
    isFormValid = validateRequired(document.getElementById('dob'), 'dobError') && isFormValid;

    // Skills validation
    if (!validateRequired(skillsDropdown, 'skillsError')) {
        isFormValid = false;
    } else if (skillsDropdown.value === 'Other' && otherSkillInput.value.trim() === '') {
        displayErrorMessage('skillsError', 'Please specify your other skill.');
        isFormValid = false;
    } else if (skillsDropdown.value === 'Other' && otherSkillInput.value.trim() !== '') {
         clearErrorMessage('skillsError'); // Clear if 'Other' is selected and input is provided
    }


    // Government ID validation
    isFormValid = validateGovtId() && isFormValid;

    // File input validations
    isFormValid = validateFileSizeAndType(govtIdPhotoInput, 'govtIdPhotoError') && isFormValid;
    isFormValid = validateFileSizeAndType(selfPhotoInput, 'selfPhotoError') && isFormValid;
    
    isFormValid = validateRequired(document.getElementById('experience'), 'experienceError') && isFormValid;

    // Preferred Job Location validation
    if (selectedLocations.size === 0) {
        displayErrorMessage('preferredLocationError', 'At least one preferred job location is required.');
        isFormValid = false;
    } else if (selectedLocations.has('Other') && otherLocationInput.value.trim() === '') {
        displayErrorMessage('preferredLocationError', 'Please specify your other preferred location.');
        isFormValid = false;
    } else {
         clearErrorMessage('preferredLocationError'); // Clear if valid
    }

    // Contact Number validations
    isFormValid = validateContactNumber(contactNumberInput, 'contactNumberError') && isFormValid;
    if (alternativeContactNumberInput.value.trim() !== '') {
        isFormValid = validateContactNumber(alternativeContactNumberInput, 'alternativeContactNumberError') && isFormValid;
    } else {
        clearErrorMessage('alternativeContactNumberError'); // Clear if empty
    }

    // Bank Details Validation
    if (!selectedBank) {
        displayErrorMessage('bankNameError', 'Bank Name is required.');
        isFormValid = false;
    } else if (selectedBank === 'Other' && otherBankNameInput.value.trim() === '') {
        displayErrorMessage('bankNameError', 'Please specify your bank name.');
        isFormValid = false;
    } else {
        clearErrorMessage('bankNameError'); // Clear if valid
    }
    isFormValid = validateRequired(document.getElementById('accountHolderName'), 'accountHolderNameError') && isFormValid;
    isFormValid = validateRequired(document.getElementById('branchName'), 'branchNameError') && isFormValid;
    isFormValid = validateAccountNumbers() && isFormValid;
    isFormValid = validateIFSCCode() && isFormValid;
    isFormValid = validateFileSizeAndType(passbookPhotoInput, 'passbookPhotoError') && isFormValid;


    // --- Form Submission / Confirmation Logic ---
    if (isFormValid) {
        // Collect all form data for display
        const fullName = document.getElementById('fullName').value;
        const address = document.getElementById('address').value;
        const pincode = document.getElementById('pincode').value;
        const state = stateSelect.value;
        const district = districtSelect.value;
        const gender = document.getElementById('gender').value;
        const dob = document.getElementById('dob').value;
        const skills = skillsDropdown.value === 'Other' ? otherSkillInput.value : skillsDropdown.value;
        const govtIdType = govtIdTypeSelect.value;
        const govtIdNumber = govtIdNumberInput.value;
        const experience = document.getElementById('experience').value;
        const prevCompany = document.getElementById('prevCompany').value || "N/A"; // Handle optional field
        
        // For preferred locations, join them and handle "Other" if selected
        const preferredLocationsArray = Array.from(selectedLocations).map(loc => loc === 'Other' && otherLocationInput.value.trim() !== '' ? otherLocationInput.value.trim() : loc);
        const preferredLocationsText = preferredLocationsArray.join(', ');

        const contactNumber = contactNumberInput.value;
        const alternativeContactNumber = alternativeContactNumberInput.value || "N/A"; // Handle optional field

        const accountHolderName = document.getElementById('accountHolderName').value;
        const bankName = selectedBank === 'Other' ? otherBankNameInput.value : selectedBank;
        const branchName = document.getElementById('branchName').value;
        const accountNumber = accountNumberInput.value;
        const ifscCode = ifscCodeInput.value;

        // Populate confirmation page spans
        document.getElementById('displayFullName').textContent = fullName;
        document.getElementById('displayAddress').textContent = address;
        document.getElementById('displayPincode').textContent = pincode;
        document.getElementById('displayState').textContent = state;
        document.getElementById('displayDistrict').textContent = district;
        document.getElementById('displayGender').textContent = gender;
        document.getElementById('displayDob').textContent = dob;
        document.getElementById('displaySkills').textContent = skills;
        document.getElementById('displayGovtIdType').textContent = govtIdType;
        document.getElementById('displayGovtIdNumber').textContent = govtIdNumber;
        document.getElementById('displayExperience').textContent = experience;
        document.getElementById('displayPrevCompany').textContent = prevCompany;
        document.getElementById('displayPreferredLocations').textContent = preferredLocationsText;
        document.getElementById('displayContactNumber').textContent = contactNumber;
        document.getElementById('displayAlternativeContactNumber').textContent = alternativeContactNumber;

        document.getElementById('displayAccountHolderName').textContent = accountHolderName;
        document.getElementById('displayBankName').textContent = bankName;
        document.getElementById('displayBranchName').textContent = branchName;
        document.getElementById('displayAccountNumber').textContent = accountNumber;
        document.getElementById('displayIfscCode').textContent = ifscCode;

        // Hide the registration form and show the confirmation page
        registrationFormSection.style.display = 'none';
        confirmationPageSection.style.display = 'block';

        // Scroll to the top of the confirmation page
        window.scrollTo({ top: 0, behavior: 'smooth' });

    } else {
        alert('Please correct the errors in the form.');
        // Find the first error element (either input-error or visible error-message)
        const firstErrorElement = document.querySelector('.input-error, .error-message[style*="block"]');
        if (firstErrorElement) {
            firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

// Function to go back to the registration form
function goBackToRegistration() {
    registrationFormSection.style.display = 'block';
    confirmationPageSection.style.display = 'none';
    // Scroll to the top of the form
    window.scrollTo({ top: 0, behavior: 'smooth' });
}