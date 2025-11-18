// FAQ data with keywords and responses
const faqData = {
  // Document Requests
  clearance: {
    keywords: ["clearance", "barangay clearance", "brgy clearance", "request clearance"],
    response: "To request a Barangay Clearance: Go to 'Document Requests' → Select 'Barangay Clearance' → Fill out the form → Submit. Processing takes 3-5 business days. You'll need a valid ID for pickup."
  },
  indigency: {
    keywords: ["indigency", "certificate of indigency", "indigent", "poor"],
    response: "For a Certificate of Indigency: Go to 'Document Requests' → Choose 'Certificate of Indigency' → Complete the form with required details → Submit. This certificate is FREE and typically processed within 3-5 days."
  },
  residency: {
    keywords: ["residency", "proof of residency", "residence certificate", "resident"],
    response: "To get Proof of Residency: Navigate to 'Document Requests' → Select 'Proof of Residency' → Fill in your details → Submit request. Bring a valid ID when claiming."
  },
  business_permit: {
    keywords: ["business permit", "brgy business", "business clearance", "permit business"],
    response: "For Barangay Business Permit: Go to 'Document Requests' → Choose 'Barangay Business Permit' → Provide business details → Submit. Requirements: DTI/SEC registration, lease contract, and valid ID."
  },
  barangay_id: {
    keywords: ["barangay id", "brgy id", "resident id", "id card"],
    response: "To apply for Barangay ID: Visit 'Document Requests' → Select 'Barangay ID' → Fill application → Submit with 1x1 photo. Processing: 7-10 days. Fee: ₱50."
  },
  job_seeker: {
    keywords: ["job seeker", "first time job", "employment certificate", "jobseeker"],
    response: "For First Time Job Seeker Certificate: Go to 'Document Requests' → Select 'First Time Job Seeker Certificate' → Complete form → Submit. FREE for first-time applicants. Bring school ID and valid ID when claiming."
  },

  // Navigation & Profile
  profile: {
    keywords: ["profile", "view profile", "my profile", "edit profile", "personal information"],
    response: "To view your profile: Click 'Profile' in the sidebar menu. Here you can see your personal information, contact details, and household data. To edit, click the 'Edit Profile' button."
  },
  dashboard: {
    keywords: ["dashboard", "home", "main page", "overview"],
    response: "The Dashboard shows your pending requests, completed documents, and recent announcements. Access it by clicking 'Dashboard' in the sidebar or the home icon."
  },
  
  // Request Tracking
  pending: {
    keywords: ["pending", "pending requests", "status", "track request", "my requests"],
    response: "To check pending requests: Go to Dashboard → Look at 'Pending Requests' section. You can also track your document status in the 'Document Requests' page."
  },
  completed: {
    keywords: ["completed", "ready", "pickup", "claim document", "finished"],
    response: "Completed documents appear in the 'Completed Requests (Ready for Pickup)' section on your Dashboard. Bring a valid ID to claim your documents at the barangay office during office hours (8AM-5PM, Mon-Fri)."
  },

  // Announcements & Events
  announcement: {
    keywords: ["announcement", "announcements", "news", "updates", "events"],
    response: "To view announcements: Click 'Announcement' in the sidebar. Here you'll find the latest barangay news, events, programs, and important notices."
  },
  calendar: {
    keywords: ["calendar", "schedule", "events calendar", "upcoming events"],
    response: "Check the 'Calendar' page to see upcoming barangay events, meetings, and important dates. You can view events by month and get details about each activity."
  },

  // Officials
  officials: {
    keywords: ["officials", "barangay officials", "captain", "kagawad", "leaders", "chairman"],
    response: "To see barangay officials: Click 'Officials' in the sidebar. This page displays information about our Barangay Chairman, Kagawads, SK Chairman, and other local leaders."
  },

  // Requirements & Fees
  requirements: {
    keywords: ["requirements", "what do i need", "documents needed", "required"],
    response: "Common requirements: Valid ID (any government-issued ID), proof of residency, and completed application form. Specific requirements vary by document type. Check the Document Requests page for detailed requirements."
  },
  fees: {
    keywords: ["fee", "fees", "cost", "price", "how much", "payment", "pay"],
    response: "Document fees: Barangay Clearance - ₱50, Barangay ID - ₱50, Certificate of Indigency - FREE, Business Permit - ₱100. Payments accepted at the barangay office. Cash only."
  },
  processing_time: {
    keywords: ["processing time", "how long", "when ready", "waiting time", "duration"],
    response: "Standard processing: 3-5 business days for most certificates. Barangay ID takes 7-10 days. Rush processing available for urgent requests (additional fee may apply). You'll be notified when ready for pickup."
  },

  // Account & Login
  logout: {
    keywords: ["logout", "log out", "sign out", "exit"],
    response: "To logout: Click the logout icon (door/exit icon) in the top-right corner of any page → Confirm logout. Always logout when using shared computers!"
  },
  password: {
    keywords: ["password", "forgot password", "reset password", "change password"],
    response: "To reset your password: Click 'Forgot Password?' on the login page → Follow instructions. For account issues, visit the barangay office with a valid ID for assistance."
  },

  // Office Information
  office_hours: {
    keywords: ["office hours", "time", "schedule", "open", "close", "when"],
    response: "Barangay Office Hours: Monday to Friday, 8:00 AM - 5:00 PM. Closed on weekends and holidays. For emergencies, contact our hotline: [ADD HOTLINE NUMBER]."
  },
  location: {
    keywords: ["location", "address", "where", "office location", "barangay hall"],
    response: "Barangay 498, Zone 49, District IV, Manila. Exact address: [ADD COMPLETE ADDRESS]. Look for the barangay hall with the blue and yellow signage."
  },
  contact: {
    keywords: ["contact", "phone", "email", "hotline", "reach", "call"],
    response: "Contact Us: Phone: [ADD PHONE], Email: [ADD EMAIL], Facebook: Barangay 498 Manila Official. For emergencies, call [ADD EMERGENCY NUMBER]."
  },

  // Help & Support
  help: {
    keywords: ["help", "assist", "support", "problem", "issue"],
    response: "Need help? You can: 1) Use this chatbot for quick answers, 2) Visit the barangay office during office hours, 3) Contact us via phone or email, 4) Check the FAQs section in Document Requests."
  },
  
  // Registration
  register: {
    keywords: ["register", "sign up", "create account", "new account", "registration"],
    response: "To register: Visit the barangay office with valid ID and proof of residency. Staff will help create your account. Registration is FREE for all residents of Barangay 498."
  }
};

// Chatbot elements
const chatBody = document.getElementById("chat-body");
const chatInput = document.getElementById("chat-input");
const sendBtn = document.getElementById("send-btn");
const chatToggle = document.getElementById("chat-toggle");
const chatbot = document.getElementById("chatbot");

// Add welcome message on load
window.addEventListener('DOMContentLoaded', () => {
  addMessage("Hello! I'm your Barangay 498 assistant. How can I help you today?", "bot");
});

// Add messages with typing animation
function addMessage(text, sender) {
  const messageDiv = document.createElement('div');
  messageDiv.className = sender;
  messageDiv.innerHTML = `<span>${text}</span>`;
  chatBody.appendChild(messageDiv);
  chatBody.scrollTop = chatBody.scrollHeight;
}

// Enhanced keyword matching function
function findBestMatch(userInput) {
  const input = userInput.toLowerCase().trim();
  let bestMatch = null;
  let highestScore = 0;

  for (const [key, data] of Object.entries(faqData)) {
    for (const keyword of data.keywords) {
      // Exact match gets highest score
      if (input === keyword.toLowerCase()) {
        return data.response;
      }
      
      // Calculate match score based on keyword presence
      if (input.includes(keyword.toLowerCase())) {
        const score = keyword.length; // Longer keywords = better match
        if (score > highestScore) {
          highestScore = score;
          bestMatch = data.response;
        }
      }
      
      // Check if keyword is in input (partial match)
      if (keyword.toLowerCase().includes(input) && input.length > 3) {
        const score = input.length;
        if (score > highestScore) {
          highestScore = score;
          bestMatch = data.response;
        }
      }
    }
  }

  return bestMatch;
}

// Handle user messages
function handleMessage(inputText) {
  if (!inputText || inputText.trim() === '') return;

  const userMessage = inputText.trim();
  addMessage(userMessage, "user");

  // Find best matching response
  let response = findBestMatch(userMessage);

  // Default response if no match found
  if (!response) {
    response = "I'm not sure about that. You can try asking about: document requests, tracking requests, announcements, officials, office hours, or fees. Or visit the barangay office for personalized assistance!";
  }

  // Simulate typing delay
  setTimeout(() => {
    addMessage(response, "bot");
  }, 500);

  chatInput.value = '';
}

// Event listeners
sendBtn.addEventListener("click", () => {
  handleMessage(chatInput.value);
});

chatInput.addEventListener("keypress", (e) => {
  if (e.key === "Enter") {
    handleMessage(chatInput.value);
  }
});

// Quick question buttons
document.querySelectorAll(".quick-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    handleMessage(btn.textContent);
  });
});

// Toggle chatbot visibility
chatToggle.onclick = () => {
  chatbot.style.display = chatbot.style.display === "flex" ? "none" : "flex";
  if (chatbot.style.display === "flex") {
    chatInput.focus();
  }
};

// Close chatbot when clicking outside
document.addEventListener('click', (e) => {
  if (!chatbot.contains(e.target) && !chatToggle.contains(e.target)) {
    // chatbot.style.display = "none"; // Optional: auto-close
  }
});