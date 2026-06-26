<aside class="left-sidebar">
    <div class="slimscroll-left-sidebar">
        <nav class="sidebar-nav">
            @php
                $s = $sidebarState ?? [];
                $authUser = auth()->user();
                $avatarPath = $authUser?->employee?->avatar_path ?: 'assets/img/user/default.jpg';
            @endphp
            <div class="sidebar-header text-center">
                <figure class="side-user-bg" style="background-image: url('{{ asset('assets/img/sidebar.jpg') }}');">
                    <img src="assets/img/sidebar.jpg" alt="" style="display: none;">
                </figure>

                <img
                    class="sidebar-user-avatar"
                    src="{{ asset($avatarPath) }}"
                    alt="profile image"
                >

                <h5 class="text-center font-weight-medium">
                    {{ $authUser?->name ?? 'HR Payroll User' }}
                </h5>
            </div>

            <ul class="sidebar-menu">
                <li id="menu-dashboard" data-id="menu-dashboard" class="main {{ ($s['isDashboard'] ?? false) ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="icon-grid"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li id="menu-organization-structure" data-id="menu-organization-structure" class="main {{ ($s['isOrganizationStructure'] ?? false) ? 'active' : '' }}">
                    <a href="{{ route('organization.structure') }}">
                        <i class="icon-share-alt"></i>
                        <span>Organization Structure</span>
                    </a>
                </li>

                @if(($s['canEmployeeView'] ?? false) || ($s['canEmployeeCreate'] ?? false) || ($s['canEmployeeUpdate'] ?? false) || ($s['canResignationApply'] ?? false) || ($s['canResignationSupervisorApprove'] ?? false) || ($s['canResignationFinalApprove'] ?? false) || ($s['canEmployeeStatusView'] ?? false) || ($s['canDepartmentView'] ?? false) || ($s['canDepartmentCreate'] ?? false) || ($s['canDesignationView'] ?? false) || ($s['canDesignationCreate'] ?? false))
                    <li id="menu-employees" data-id="menu-employees" class="main {{ ($s['isEmployees'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isEmployees'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-user"></i>
                            <span>Employees</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isEmployees'] ?? false) ? 'true' : 'false' }}">
                            @if($s['canEmployeeView'] ?? false)
                                <li class="{{ request()->routeIs('employees.index') || request()->routeIs('employees.show') || request()->routeIs('employees.edit') ? 'active' : '' }}"><a href="{{ route('employees.index') }}">Employee List</a></li>
                            @endif
                            @if($s['canEmployeeCreate'] ?? false)
                                <li class="{{ request()->routeIs('employees.create') ? 'active' : '' }}"><a href="{{ route('employees.create') }}">Add Employee</a></li>
                            @endif
                            @if($s['canEmployeeUpdate'] ?? false)
                                <li class="{{ request()->routeIs('employees.profile-updates.index') || request()->routeIs('employees.profile-updates.show') ? 'active' : '' }}"><a href="{{ route('employees.profile-updates.index') }}">Update Approval Queue</a></li>
                            @endif
                            @if($s['canResignationApply'] ?? false)
                                <li class="{{ request()->routeIs('employee-resignations.index') ? 'active' : '' }}"><a href="{{ route('employee-resignations.index') }}">Resignation Apply</a></li>
                            @endif
                            @if($s['canResignationSupervisorApprove'] ?? false)
                                <li class="{{ request()->routeIs('employee-resignations.supervisor-approvals') ? 'active' : '' }}"><a href="{{ route('employee-resignations.supervisor-approvals') }}">Resignation Supervisor Approval</a></li>
                            @endif
                            @if($s['canResignationFinalApprove'] ?? false)
                                <li class="{{ request()->routeIs('employee-resignations.final-approvals') ? 'active' : '' }}"><a href="{{ route('employee-resignations.final-approvals') }}">Resignation Final Approval</a></li>
                            @endif
                            @if($s['canEmployeeStatusView'] ?? false)
                                <li class="{{ request()->routeIs('employee-statuses.*') ? 'active' : '' }}"><a href="{{ route('employee-statuses.index') }}">Employee Status</a></li>
                            @endif
                            @if($s['canDepartmentView'] ?? false)
                                <li class="{{ request()->routeIs('departments.index') || request()->routeIs('departments.edit') ? 'active' : '' }}"><a href="{{ route('departments.index') }}">Departments</a></li>
                            @endif
                            @if($s['canDesignationView'] ?? false)
                                <li class="{{ request()->routeIs('designations.index') || request()->routeIs('designations.edit') ? 'active' : '' }}"><a href="{{ route('designations.index') }}">Designations</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if(($s['canAttendanceView'] ?? false) || ($s['canAttendanceClock'] ?? false) || ($s['canAttendanceManage'] ?? false) || ($s['canAttendanceReport'] ?? false))
                    <li id="menu-attendance" data-id="menu-attendance" class="main {{ ($s['isAttendance'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isAttendance'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-clock"></i>
                            <span>Attendance</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isAttendance'] ?? false) ? 'true' : 'false' }}">
                            @if(($s['canAttendanceView'] ?? false) || ($s['canAttendanceManage'] ?? false))
                                <li class="{{ request()->routeIs('attendance.index') ? 'active' : '' }}"><a href="{{ route('attendance.index') }}">Attendances</a></li>
                            @endif
                            @if($s['canAttendanceApiIntegration'] ?? false)
                                <li class="{{ request()->routeIs('attendance.api-docs') ? 'active' : '' }}"><a href="{{ route('attendance.api-docs') }}">API Integration</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($s['canAnnouncementMenu'] ?? false)
                    <li id="menu-announcements" data-id="menu-announcements" class="main {{ ($s['isAnnouncements'] ?? false) ? 'active' : '' }}">
                        <a href="{{ route('announcements.index') }}">
                            <i class="icon-bell"></i>
                            <span>Notices & Announcements</span>
                        </a>
                    </li>
                @endif

                @if(($s['canTeamView'] ?? false) || ($s['canTeamCreate'] ?? false) || ($s['canTeamUpdate'] ?? false) || ($s['canTeamManageMembers'] ?? false) || ($s['canProjectView'] ?? false) || ($s['canProjectCreate'] ?? false) || ($s['canProjectUpdate'] ?? false) || ($s['canProjectManageMembers'] ?? false) || ($s['canTaskView'] ?? false) || ($s['canTaskCreate'] ?? false) || ($s['canTaskUpdate'] ?? false) || ($s['canTaskAssign'] ?? false) || ($s['canTaskComment'] ?? false))
                    <li id="menu-work" data-id="menu-work" class="main {{ ($s['isWork'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isWork'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-briefcase"></i>
                            <span>Teams & Projects</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isWork'] ?? false) ? 'true' : 'false' }}">
                            @if(($s['canTeamView'] ?? false) || ($s['canTeamCreate'] ?? false) || ($s['canTeamUpdate'] ?? false) || ($s['canTeamManageMembers'] ?? false))
                                <li class="{{ request()->routeIs('teams.*') ? 'active' : '' }}"><a href="{{ route('teams.index') }}">Teams</a></li>
                            @endif
                            @if(($s['canProjectView'] ?? false) || ($s['canProjectCreate'] ?? false) || ($s['canProjectUpdate'] ?? false) || ($s['canProjectManageMembers'] ?? false))
                                <li class="{{ request()->routeIs('projects.*') ? 'active' : '' }}"><a href="{{ route('projects.index') }}">Projects</a></li>
                            @endif
                            @if(($s['canTaskView'] ?? false) || ($s['canTaskCreate'] ?? false) || ($s['canTaskUpdate'] ?? false) || ($s['canTaskAssign'] ?? false) || ($s['canTaskComment'] ?? false))
                                <li class="{{ request()->routeIs('tasks.*') ? 'active' : '' }}"><a href="{{ route('tasks.index') }}">Tasks</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if(($s['canLeaveView'] ?? false) || ($s['canLeaveManageCategories'] ?? false) || ($s['canLeaveManageQuotas'] ?? false))
                    <li id="menu-leave" data-id="menu-leave" class="main {{ ($s['isLeave'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isLeave'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-calendar"></i>
                            <span>Leave Management</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isLeave'] ?? false) ? 'true' : 'false' }}">
                            @if(($s['canLeaveView'] ?? false) || ($s['canLeaveManageQuotas'] ?? false))
                                <li class="{{ request()->routeIs('leave-balances.*') ? 'active' : '' }}"><a href="{{ route('leave-balances.index') }}">Leave Balances</a></li>
                            @endif
                            @if($s['canLeaveApply'] ?? false)
                                <li class="{{ request()->routeIs('leave-applications.*') ? 'active' : '' }}"><a href="{{ route('leave-applications.index') }}">Apply Leave</a></li>
                            @endif
                            @if($s['canLeaveApprove'] ?? false)
                                <li class="{{ request()->routeIs('leave-approvals.*') ? 'active' : '' }}"><a href="{{ route('leave-approvals.index') }}">Leave Approvals</a></li>
                            @endif
                            @if(($s['canLeaveReport'] ?? false) || ($s['canLeaveApprove'] ?? false) || ($s['canLeaveView'] ?? false))
                                <li class="{{ request()->routeIs('leave-reports.*') ? 'active' : '' }}"><a href="{{ route('leave-reports.index') }}">Leave Reports</a></li>
                            @endif
                            @if($s['canLeaveManageCategories'] ?? false)
                                <li class="{{ request()->routeIs('leave-categories.*') ? 'active' : '' }}"><a href="{{ route('leave-categories.index') }}">Leave Categories</a></li>
                            @endif
                            @if($s['canLeaveManageQuotas'] ?? false)
                                <li class="{{ request()->routeIs('leave-policies.*') ? 'active' : '' }}"><a href="{{ route('leave-policies.index') }}">Leave Policies</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($s['canPayrollMenu'] ?? false)
                    <li id="menu-payroll" data-id="menu-payroll" class="main {{ ($s['isPayroll'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isPayroll'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-wallet"></i>
                            <span>Payroll</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isPayroll'] ?? false) ? 'true' : 'false' }}">
                            @if(($s['canPayrollView'] ?? false) || ($s['canPayrollGenerate'] ?? false))
                                <li class="{{ request()->routeIs('payroll.runs.*') || request()->routeIs('payroll.items.*') ? 'active' : '' }}"><a href="{{ route('payroll.runs.index') }}">Payroll Runs</a></li>
                            @endif
                            @if($s['canPayrollSalaryGrades'] ?? false)
                                <li class="{{ request()->routeIs('salary-grades.*') ? 'active' : '' }}"><a href="{{ route('salary-grades.index') }}">Salary Grades</a></li>
                            @endif
                            @if($s['canPayrollSalaryTemplates'] ?? false)
                                <li class="{{ request()->routeIs('payroll.salary-templates.*') || request()->routeIs('payroll.salary-template-assignments.*') ? 'active' : '' }}"><a href="{{ route('payroll.salary-templates.index') }}">Salary Templates</a></li>
                            @endif
                            @if($s['canPayrollManageBonus'] ?? false)
                                <li class="{{ request()->routeIs('payroll.bonuses.*') ? 'active' : '' }}"><a href="{{ route('payroll.bonuses.index') }}">Bonuses</a></li>
                            @endif
                            @if($s['canPayrollManageLoan'] ?? false)
                                <li class="{{ request()->routeIs('payroll.loans.*') ? 'active' : '' }}"><a href="{{ route('payroll.loans.index') }}">Loans</a></li>
                            @endif
                            @if($s['canPayrollManageDeduction'] ?? false)
                                <li class="{{ request()->routeIs('payroll.deductions.*') ? 'active' : '' }}"><a href="{{ route('payroll.deductions.index') }}">Deductions</a></li>
                            @endif
                            @if($s['canPayrollManagePf'] ?? false)
                                <li class="{{ request()->routeIs('payroll.provident-funds.*') ? 'active' : '' }}"><a href="{{ route('payroll.provident-funds.index') }}">Provident Fund</a></li>
                            @endif
                            @if(($s['canPayrollReport'] ?? false) && !($s['canPayrollView'] ?? false))
                                <li class="{{ request()->routeIs('reports.payroll') ? 'active' : '' }}"><a href="{{ route('reports.payroll') }}">Payslips</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if(($s['canHolidayView'] ?? false) || ($s['canHolidayCreate'] ?? false) || ($s['canHolidayUpdate'] ?? false))
                    <li id="menu-holiday" data-id="menu-holiday" class="main {{ ($s['isHoliday'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isHoliday'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-plane"></i>
                            <span>Holidays</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isHoliday'] ?? false) ? 'true' : 'false' }}">
                            @if($s['canHolidayView'] ?? false)
                                <li class="{{ request()->routeIs('holidays.index') ? 'active' : '' }}"><a href="{{ route('holidays.index') }}">Holiday List</a></li>
                            @endif
                            @if($s['canHolidayCreate'] ?? false)
                                <li class="{{ request()->routeIs('holidays.create') ? 'active' : '' }}"><a href="{{ route('holidays.create') }}">Add Holiday</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($s['canReportMenu'] ?? false)
                <li id="menu-reports" data-id="menu-reports" class="main {{ ($s['isReports'] ?? false) ? 'active' : '' }}">
                    <a class="has-arrow" href="#" aria-expanded="{{ ($s['isReports'] ?? false) ? 'true' : 'false' }}">
                        <i class="icon-chart"></i>
                        <span>Reports</span>
                    </a>
                    <ul aria-expanded="{{ ($s['isReports'] ?? false) ? 'true' : 'false' }}">
                        @if($s['canEmployeeReport'] ?? false)
                            <li class="{{ request()->routeIs('reports.employees') ? 'active' : '' }}"><a href="{{ route('reports.employees') }}">Employee Report</a></li>
                        @endif
                        @if($s['canAttendanceReportMenu'] ?? false)
                            <li class="{{ request()->routeIs('reports.attendance') ? 'active' : '' }}"><a href="{{ route('reports.attendance') }}">Attendance Report</a></li>
                        @endif
                        @if($s['canLeaveReportMenu'] ?? false)
                            <li class="{{ request()->routeIs('leave-reports.*') ? 'active' : '' }}"><a href="{{ route('leave-reports.index') }}">Leave Report</a></li>
                        @endif
                        @if($s['canPayrollReportMenu'] ?? false)
                            <li class="{{ request()->routeIs('reports.payroll') ? 'active' : '' }}"><a href="{{ route('reports.payroll') }}">Payroll Report</a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if($s['canUserManagementMenu'] ?? false)
                    <li id="menu-user-management" data-id="menu-user-management" class="main {{ ($s['isUserManagement'] ?? false) ? 'active' : '' }}">
                        <a class="has-arrow" href="#" aria-expanded="{{ ($s['isUserManagement'] ?? false) ? 'true' : 'false' }}">
                            <i class="icon-lock"></i>
                            <span>User Management</span>
                        </a>
                        <ul aria-expanded="{{ ($s['isUserManagement'] ?? false) ? 'true' : 'false' }}">
                            @if($s['canUserList'] ?? false)
                                <li class="{{ request()->routeIs('users.index') || request()->routeIs('users.edit') || request()->routeIs('users.approval') ? 'active' : '' }}"><a href="{{ route('users.index') }}">User List</a></li>
                            @endif
                            @if($s['canUserCreate'] ?? false)
                                <li class="{{ request()->routeIs('users.create') ? 'active' : '' }}"><a href="{{ route('users.create') }}">Add User</a></li>
                            @endif
                            @if(($s['canRoleView'] ?? false) || ($s['canRoleCreate'] ?? false) || ($s['canRoleUpdate'] ?? false) || ($s['canRoleAssign'] ?? false))
                                <li class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"><a href="{{ route('roles.index') }}">Roles</a></li>
                            @endif
                            @if($s['canPermissionsMenu'] ?? false)
                                <li class="{{ request()->routeIs('permissions.*') ? 'active' : '' }}"><a href="{{ route('permissions.index') }}">Permissions</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if($s['canSettingsView'] ?? false)
                    <li id="menu-settings" data-id="menu-settings" class="main {{ ($s['isSettings'] ?? false) ? 'active' : '' }}">
                        <a href="{{ route('settings.edit') }}">
                            <i class="icon-settings"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
