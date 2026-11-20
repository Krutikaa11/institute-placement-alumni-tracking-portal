/**
 * Institute Placement & Alumni Tracking Portal
 * AngularJS + ES6 Application Controller
 * Module: placementPortalApp
 */

const portalApp = angular.module('placementPortalApp', []);

portalApp.controller('PortalCtrl', ['$scope', '$http', '$timeout', '$window', function($scope, $http, $timeout, $window) {
  // State
  $scope.currentUser = null;
  $scope.activeView = 'home';
  $scope.kpis = {};
  $scope.chartsData = {};

  // Data Collections
  $scope.drives = [];
  $scope.alumni = [];
  $scope.companies = [];
  $scope.applications = [];
  $scope.studentApplications = [];
  $scope.placements = [];
  $scope.adminUsers = [];
  $scope.studentProfile = {};
  $scope.alumniProfile = {};

  // Filters & Search
  $scope.driveFilter = { search: '', branch: '', status: 'active' };
  $scope.alumniFilter = { search: '', branch: '', year: '', mentorshipOnly: false };
  $scope.appFilterStatus = '';

  // Forms State
  $scope.loginData = { email: '', password: '', role: '' };
  $scope.regData = { role: 'student', name: '', email: '', password: '', branch: 'Computer Science', year: 4, cgpa: '', phone: '', roll_no: '', graduation_year: 2022, company: '', job_role: '', linkedin: '' };
  $scope.activeDrive = {};
  $scope.driveForm = { id: 0, company_id: '', job_role: '', package: '', location: '', drive_date: '', deadline: '', min_cgpa: 7.0, eligible_branches: 'Computer Science, Information Technology, Electronics & Comm', skills: '', vacancies: 10, status: 'active' };
  $scope.statusModalData = { application_id: 0, status: 'Applied', notes: '' };
  $scope.mentorModalData = { alumni_id: 0, target_name: '', message: '' };

  // Toast Notification System
  $scope.showToast = function(message, type = 'info') {
    const toastEl = document.getElementById('portalToast');
    const toastBody = document.getElementById('toastBody');
    const toastIcon = document.getElementById('toastIcon');

    if (!toastEl || !toastBody) {
      alert(message);
      return;
    }

    const iconMap = {
      success: 'bi-check-circle-fill text-success',
      danger: 'bi-x-circle-fill text-danger',
      warning: 'bi-exclamation-triangle-fill text-warning',
      info: 'bi-info-circle-fill text-primary'
    };

    if (toastIcon) {
      toastIcon.className = `bi ${iconMap[type] || iconMap.info} me-2 fs-5`;
    }
    toastBody.textContent = message;

    const bsToast = bootstrap.Toast.getOrCreateInstance(toastEl);
    bsToast.show();
  };

  // View Navigation
  $scope.navigate = function(viewName, updateHash = true) {
    if (updateHash) {
      $window.location.hash = viewName;
    }
    $scope.activeView = viewName;

    // Check Role Access
    const roleRoutes = {
      'student-': 'student',
      'alumni-': 'alumni',
      'officer-': 'officer',
      'admin-': 'admin'
    };

    for (const [prefix, requiredRole] of Object.entries(roleRoutes)) {
      if (viewName.startsWith(prefix)) {
        if (!$scope.currentUser) {
          $scope.showToast('Please log in to access this page.', 'warning');
          $scope.navigate('login');
          return;
        }
        if ($scope.currentUser.role !== requiredRole && $scope.currentUser.role !== 'admin') {
          $scope.showToast(`Access denied. Requires ${requiredRole} privileges.`, 'danger');
          $scope.navigate('home');
          return;
        }
      }
    }

    $window.scrollTo({ top: 0, behavior: 'smooth' });

    // Close mobile sidebar
    const sidebar = document.querySelector('.dashboard-sidebar');
    if (sidebar) sidebar.classList.remove('show');

    // Trigger View Load
    $scope.handleViewLoad(viewName);
  };

  $scope.handleViewLoad = function(viewName) {
    switch (viewName) {
      case 'home':
        $scope.loadHomeData();
        break;
      case 'drives':
        $scope.loadDrives();
        break;
      case 'alumni':
        $scope.loadAlumni();
        break;
      case 'statistics':
        $scope.loadStatistics();
        break;
      case 'student-dashboard':
        $scope.loadStudentDashboard();
        break;
      case 'student-applications':
        $scope.loadStudentApplications();
        break;
      case 'student-profile':
        $scope.loadStudentProfile();
        break;
      case 'alumni-dashboard':
        $scope.loadAlumniDashboard();
        break;
      case 'alumni-profile':
        $scope.loadAlumniProfile();
        break;
      case 'officer-dashboard':
        $scope.loadOfficerDashboard();
        break;
      case 'officer-drives':
        $scope.loadOfficerDrives();
        break;
      case 'officer-applications':
        $scope.loadOfficerApplications();
        break;
      case 'officer-placements':
        $scope.loadOfficerPlacements();
        break;
      case 'admin-dashboard':
        $scope.loadAdminDashboard();
        break;
      case 'admin-students':
        $scope.loadAdminStudents();
        break;
      case 'admin-alumni':
        $scope.loadAdminAlumni();
        break;
      case 'admin-companies':
        $scope.loadAdminCompanies();
        break;
      case 'admin-drives':
        $scope.loadOfficerDrives();
        break;
      case 'admin-users':
        $scope.loadAdminUsers();
        break;
      case 'reports':
        break;
    }
  };

  // Auth Operations
  $scope.checkAuth = function() {
    return $http.get('api/auth/me.php').then(function(res) {
      if (res.data && res.data.authenticated && res.data.user) {
        $scope.currentUser = res.data.user;
      } else {
        $scope.currentUser = null;
      }
    });
  };

  $scope.login = function() {
    $http.post('api/auth/login.php', $scope.loginData).then(function(res) {
      if (res.data && res.data.success) {
        $scope.currentUser = res.data.user;
        $scope.showToast(`Welcome back, ${res.data.user.name}!`, 'success');

        const dashboardMap = {
          student: 'student-dashboard',
          alumni: 'alumni-dashboard',
          officer: 'officer-dashboard',
          admin: 'admin-dashboard'
        };
        $scope.navigate(dashboardMap[res.data.user.role] || 'home');
      } else {
        $scope.showToast(res.data.message || 'Login failed.', 'danger');
      }
    }, function(err) {
      $scope.showToast(err.data?.message || 'Invalid email or password.', 'danger');
    });
  };

  $scope.quickDemoLogin = function(role) {
    const creds = {
      admin: { email: 'admin@sanjivani.edu.in', password: 'Admin@123' },
      officer: { email: 'tpo@sanjivani.edu.in', password: 'Officer@123' },
      student: { email: 'rohan.kulkarni@sanjivani.edu.in', password: 'Student@123' },
      alumni: { email: 'priya.nair@sanjivani.edu.in', password: 'Alumni@123' }
    };
    if (creds[role]) {
      $scope.loginData = Object.assign({}, creds[role], { role: role });
      $scope.login();
    }
  };

  $scope.logout = function() {
    $http.post('api/auth/logout.php', {}).then(function() {
      $scope.currentUser = null;
      $scope.showToast('You have been logged out.', 'info');
      $scope.navigate('home');
    });
  };

  $scope.register = function() {
    $http.post('api/auth/register.php', $scope.regData).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Registration successful! Welcome to the portal.', 'success');
        $scope.checkAuth().then(function() {
          $scope.navigate($scope.regData.role === 'student' ? 'student-dashboard' : 'alumni-dashboard');
        });
      } else {
        $scope.showToast(res.data.message || 'Registration failed.', 'danger');
      }
    }, function(err) {
      $scope.showToast(err.data?.message || 'Registration error.', 'danger');
    });
  };

  // Home Data
  $scope.loadHomeData = function() {
    $http.get('api/statistics/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.kpis = res.data.kpis;
      }
    });

    $http.get('api/drives/index.php?status=active').then(function(res) {
      if (res.data && res.data.success) {
        $scope.latestDrives = res.data.data.slice(0, 3);
      }
    });

    $http.get('api/companies/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.companies = res.data.data;
      }
    });
  };

  // Drives
  $scope.loadDrives = function() {
    const params = {
      search: $scope.driveFilter.search || '',
      branch: $scope.driveFilter.branch || '',
      status: $scope.driveFilter.status || ''
    };
    $http.get('api/drives/index.php', { params: params }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.drives = res.data.data;
      }
    });
  };

  $scope.showDriveDetails = function(drive) {
    $scope.activeDrive = drive;
    const modal = new bootstrap.Modal(document.getElementById('driveDetailModal'));
    modal.show();
  };

  $scope.showEligibility = function(drive) {
    $http.get(`api/drives/eligibility.php?drive_id=${drive.id}`).then(function(res) {
      if (res.data && res.data.success) {
        $scope.eligibilityReport = res.data;
        const modal = new bootstrap.Modal(document.getElementById('eligibilityModal'));
        modal.show();
      }
    });
  };

  $scope.openApplyModal = function(drive) {
    $scope.applyDriveTarget = drive;
    document.getElementById('applyNotes').value = '';
    const modal = new bootstrap.Modal(document.getElementById('applyDriveModal'));
    modal.show();
  };

  $scope.submitApplication = function() {
    const form = document.getElementById('applyDriveForm');
    const formData = new FormData(form);
    formData.append('drive_id', $scope.applyDriveTarget.id);

    fetch('api/applications/apply.php', {
      method: 'POST',
      body: formData
    }).then(res => res.json()).then(data => {
      if (data.success) {
        $scope.showToast('Application submitted successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('applyDriveModal')).hide();
        $scope.loadDrives();
      } else {
        $scope.showToast(data.message || 'Application failed.', 'danger');
      }
    }).catch(err => {
      $scope.showToast('Failed to submit application.', 'danger');
    });
  };

  // Alumni Directory & Mentorship
  $scope.loadAlumni = function() {
    const params = {
      search: $scope.alumniFilter.search || '',
      branch: $scope.alumniFilter.branch || '',
      graduation_year: $scope.alumniFilter.year || '',
      mentorship_only: $scope.alumniFilter.mentorshipOnly
    };
    $http.get('api/alumni/index.php', { params: params }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.alumni = res.data.data;
      }
    });
  };

  $scope.openMentorshipModal = function(alum) {
    $scope.mentorModalData = {
      alumni_id: alum.id,
      target_name: `${alum.name} (${alum.company || 'Alumni'})`,
      message: ''
    };
    const modal = new bootstrap.Modal(document.getElementById('mentorshipRequestModal'));
    modal.show();
  };

  $scope.submitMentorshipRequest = function() {
    if (!$scope.mentorModalData.message.trim()) {
      $scope.showToast('Please write a personalized message.', 'warning');
      return;
    }
    $http.post('api/mentorship/request.php', {
      alumni_id: $scope.mentorModalData.alumni_id,
      message: $scope.mentorModalData.message
    }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast(res.data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('mentorshipRequestModal')).hide();
      } else {
        $scope.showToast(res.data.message || 'Request failed.', 'danger');
      }
    });
  };

  // Student Dashboard
  $scope.loadStudentDashboard = function() {
    $http.get('api/students/profile.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.studentProfile = res.data.data;
      }
    });

    $http.get('api/applications/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.studentApplications = res.data.data;
        $scope.studentInterviewsCount = $scope.studentApplications.filter(a => a.status === 'Interview').length;
        $scope.studentSelectedCount = $scope.studentApplications.filter(a => a.status === 'Selected').length;
      }
    });

    $http.get('api/drives/index.php?status=active').then(function(res) {
      if (res.data && res.data.success) {
        $scope.studentEligibleDrives = res.data.data.filter(d => d.is_eligible);
      }
    });
  };

  $scope.loadStudentApplications = function() {
    $http.get('api/applications/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.studentApplications = res.data.data;
      }
    });
  };

  $scope.loadStudentProfile = function() {
    $http.get('api/students/profile.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.studentProfile = res.data.data;
      }
    });
  };

  $scope.saveStudentProfile = function() {
    $http.post('api/students/profile.php', $scope.studentProfile).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Profile updated successfully!', 'success');
        $scope.checkAuth();
      } else {
        $scope.showToast(res.data.message || 'Update failed.', 'danger');
      }
    });
  };

  $scope.uploadStudentResume = function() {
    const fileInput = document.getElementById('fileResumeUpload');
    if (!fileInput.files || fileInput.files.length === 0) {
      $scope.showToast('Please select a PDF or DOCX file.', 'warning');
      return;
    }

    const formData = new FormData();
    formData.append('resume', fileInput.files[0]);

    fetch('api/students/upload_resume.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          $scope.showToast('Resume uploaded and verified!', 'success');
          $scope.loadStudentProfile();
        } else {
          $scope.showToast(data.message || 'Upload failed.', 'danger');
        }
      });
  };

  // Alumni Dashboard
  $scope.loadAlumniDashboard = function() {
    $http.get('api/mentorship/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.alumniRequests = res.data.data;
        $scope.alumniPendingCount = $scope.alumniRequests.filter(r => r.status === 'Pending').length;
        $scope.alumniAcceptedCount = $scope.alumniRequests.filter(r => r.status === 'Accepted').length;
      }
    });
  };

  $scope.respondMentorship = function(req, status) {
    const note = prompt(`Enter optional response note (Action: ${status}):`, status === 'Accepted' ? 'Happy to connect! Let us arrange a call.' : '');
    $http.post('api/mentorship/status.php', {
      request_id: req.id,
      status: status,
      response_message: note
    }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast(`Request marked as ${status}!`, 'success');
        $scope.loadAlumniDashboard();
      }
    });
  };

  $scope.loadAlumniProfile = function() {
    $http.get('api/alumni/profile.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.alumniProfile = res.data.data;
        $scope.alumniProfile.available_for_mentorship = ($scope.alumniProfile.available_for_mentorship == 1);
      }
    });
  };

  $scope.saveAlumniProfile = function() {
    const payload = Object.assign({}, $scope.alumniProfile);
    payload.available_for_mentorship = payload.available_for_mentorship ? 1 : 0;
    $http.post('api/alumni/profile.php', payload).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Alumni profile updated successfully!', 'success');
        $scope.checkAuth();
      }
    });
  };

  // Placement Officer
  $scope.loadOfficerDashboard = function() {
    $http.get('api/statistics/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.kpis = res.data.kpis;
      }
    });
    $http.get('api/applications/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.officerRecentApplications = res.data.data.slice(0, 6);
      }
    });
  };

  $scope.loadOfficerDrives = function() {
    $http.get('api/drives/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.officerDrives = res.data.data;
      }
    });
  };

  $scope.openDriveModal = function(drive = null) {
    $http.get('api/companies/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.companiesList = res.data.data;
      }
    });

    if (drive) {
      $scope.driveForm = Object.assign({}, drive);
      $scope.driveFormModalTitle = 'Edit Placement Drive';
    } else {
      $scope.driveForm = {
        id: 0,
        company_id: '',
        job_role: '',
        package: '',
        location: '',
        drive_date: '',
        deadline: '',
        min_cgpa: 7.0,
        eligible_branches: 'Computer Science, Information Technology, Electronics & Comm',
        skills: '',
        vacancies: 10,
        status: 'active'
      };
      $scope.driveFormModalTitle = 'Add New Placement Drive';
    }
    const modal = new bootstrap.Modal(document.getElementById('driveModal'));
    modal.show();
  };

  $scope.saveDrive = function() {
    const method = ($scope.driveForm.id > 0) ? 'PUT' : 'POST';
    $http({
      method: method,
      url: 'api/drives/index.php',
      data: $scope.driveForm
    }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Drive saved successfully!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('driveModal')).hide();
        $scope.loadOfficerDrives();
      } else {
        $scope.showToast(res.data.message || 'Failed to save drive.', 'danger');
      }
    });
  };

  $scope.deleteDrive = function(drive) {
    if (!confirm('Are you sure you want to delete this placement drive?')) return;
    $http.delete(`api/drives/index.php?id=${drive.id}`).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Drive deleted.', 'success');
        $scope.loadOfficerDrives();
      }
    });
  };

  $scope.loadOfficerApplications = function() {
    const params = { status: $scope.appFilterStatus || '' };
    $http.get('api/applications/index.php', { params: params }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.officerApplications = res.data.data;
      }
    });
  };

  $scope.openStatusModal = function(app) {
    $scope.statusModalData = {
      application_id: app.id,
      status: app.status,
      notes: ''
    };
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
  };

  $scope.saveApplicationStatus = function() {
    $http.post('api/applications/status.php', $scope.statusModalData).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast(res.data.message, 'success');
        bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
        $scope.loadOfficerApplications();
        if ($scope.activeView === 'officer-dashboard') $scope.loadOfficerDashboard();
      } else {
        $scope.showToast(res.data.message || 'Status update failed.', 'danger');
      }
    });
  };

  $scope.loadOfficerPlacements = function() {
    $http.get('api/placements/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.placements = res.data.data;
      }
    });
  };

  $scope.deletePlacement = function(p) {
    if (!confirm('Are you sure you want to delete this placement record?')) return;
    $http.delete(`api/placements/index.php?id=${p.id}`).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Placement deleted.', 'success');
        $scope.loadOfficerPlacements();
      }
    });
  };

  // Admin Management
  $scope.loadAdminDashboard = function() {
    $http.get('api/statistics/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.kpis = res.data.kpis;
      }
    });
  };

  $scope.loadAdminStudents = function() {
    $http.get('api/students/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.adminStudents = res.data.data;
      }
    });
  };

  $scope.deleteAdminStudent = function(s) {
    if (!confirm('Delete this student account?')) return;
    $http.delete(`api/students/index.php?id=${s.id}`).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Student deleted.', 'success');
        $scope.loadAdminStudents();
      }
    });
  };

  $scope.loadAdminAlumni = function() {
    $http.get('api/alumni/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.adminAlumni = res.data.data;
      }
    });
  };

  $scope.deleteAdminAlumni = function(a) {
    if (!confirm('Delete this alumni record?')) return;
    $http.delete(`api/alumni/index.php?id=${a.id}`).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Alumni record deleted.', 'success');
        $scope.loadAdminAlumni();
      }
    });
  };

  $scope.loadAdminCompanies = function() {
    $http.get('api/companies/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.adminCompanies = res.data.data;
      }
    });
  };

  $scope.deleteAdminCompany = function(c) {
    if (!confirm('Delete this company and its drives?')) return;
    $http.delete(`api/companies/index.php?id=${c.id}`).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast('Company deleted.', 'success');
        $scope.loadAdminCompanies();
      }
    });
  };

  $scope.loadAdminUsers = function() {
    $http.get('api/auth/users.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.adminUsers = res.data.data;
      }
    });
  };

  $scope.toggleUserStatus = function(u) {
    $http.put('api/auth/users.php', { id: u.id, action: 'toggle_status' }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast(res.data.message, 'success');
        $scope.loadAdminUsers();
      }
    });
  };

  $scope.resetUserPassword = function(u) {
    const newPass = prompt('Enter new password for user:', 'Welcome@123');
    if (!newPass) return;
    $http.put('api/auth/users.php', { id: u.id, action: 'reset_password', password: newPass }).then(function(res) {
      if (res.data && res.data.success) {
        $scope.showToast(res.data.message, 'success');
      }
    });
  };

  // Statistics & Charts
  $scope.loadStatistics = function() {
    $http.get('api/statistics/index.php').then(function(res) {
      if (res.data && res.data.success) {
        $scope.kpis = res.data.kpis;
        $timeout(function() {
          PortalCharts.renderAll(res.data.charts);
        }, 100);
      }
    });
  };

  $scope.downloadReport = function(type, format) {
    $window.location.href = `api/reports/export.php?type=${type}&format=${format}`;
  };

  // Global Bridge for Native onclicks
  $window.App = {
    navigate: (v) => $scope.$apply(() => $scope.navigate(v)),
    quickDemoLogin: (r) => $scope.$apply(() => $scope.quickDemoLogin(r)),
    logout: () => $scope.$apply(() => $scope.logout()),
    downloadReport: (t, f) => $scope.downloadReport(t, f)
  };

  // Hash listener
  $window.addEventListener('hashchange', function() {
    const hash = $window.location.hash.replace('#', '');
    if (hash && hash !== $scope.activeView) {
      $scope.$apply(function() {
        $scope.navigate(hash, false);
      });
    }
  });

  // Startup
  $scope.checkAuth().then(function() {
    const hash = $window.location.hash.replace('#', '');
    $scope.navigate(hash || 'home', false);
  });
}]);
