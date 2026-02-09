# Requirements Document

## Introduction

System Sight is a workflow management system designed to help organizations document, track, and continuously improve their Standard Operating Procedures (SOPs) and business processes. The system uses a hierarchical 4-level tree structure (Machines → Subsystems → Components → Upgrades) to organize workflows, enabling teams to visualize their business operations, identify areas needing attention, and ship improvements systematically. The system emphasizes continuous improvement through streak tracking, health monitoring, and a notification system that keeps teams engaged with process optimization.

## Glossary

- **Machine**: A high-level business area or domain (e.g., Demand, Sales, Delivery)
- **Subsystem**: A functional area within a Machine that groups related processes
- **Component**: A specific process or workflow within a Subsystem that can have multiple Upgrades
- **Upgrade**: A documented procedure containing name, purpose, trigger, steps, and definition of done
- **Ship**: The action of activating/publishing an Upgrade to make it the current active version
- **Health_Status**: The operational state of a Component (smooth ✅, on_fire 🔥, needs_love 💛)
- **Streak**: A count of consecutive weeks where at least one Upgrade has been shipped
- **System**: Refers to the System Sight application
- **User**: A person who interacts with the System Sight application
- **Dashboard**: The main view showing all Machines and their health metrics
- **Quick_Ship**: A dropdown interface showing Components that need attention

## Requirements

### Requirement 1: Hierarchical Workflow Organization

**User Story:** As a business process manager, I want to organize workflows in a hierarchical structure, so that I can represent my organization's processes at different levels of granularity.

#### Acceptance Criteria

1. THE System SHALL maintain a 4-level hierarchy: Machine → Subsystem → Component → Upgrade
2. WHEN a User creates a Machine, THE System SHALL allow the User to define a name and description
3. WHEN a User creates a Subsystem, THE System SHALL require association with exactly one parent Machine
4. WHEN a User creates a Component, THE System SHALL require association with exactly one parent Subsystem
5. WHEN a User creates an Upgrade, THE System SHALL require association with exactly one parent Component
6. WHEN a User views a Machine, THE System SHALL display all child Subsystems
7. WHEN a User views a Subsystem, THE System SHALL display all child Components and recent Upgrades
8. THE System SHALL prevent deletion of a parent entity that has active children

### Requirement 2: Upgrade Documentation Structure

**User Story:** As a process documenter, I want to create structured upgrade documentation, so that anyone can understand and execute the workflow consistently.

#### Acceptance Criteria

1. WHEN a User creates an Upgrade, THE System SHALL require a name field
2. WHEN a User creates an Upgrade, THE System SHALL require a purpose field describing why the upgrade exists
3. WHEN a User creates an Upgrade, THE System SHALL require a trigger field describing when to execute the upgrade
4. WHEN a User creates an Upgrade, THE System SHALL require a steps field containing the execution instructions
5. WHEN a User creates an Upgrade, THE System SHALL require a definition_of_done field specifying completion criteria
6. THE System SHALL persist all Upgrade fields to the database
7. WHEN a User edits an Upgrade, THE System SHALL display the current values in the edit form
8. WHEN a User saves an Upgrade, THE System SHALL validate that all required fields are non-empty

### Requirement 3: Component Health Monitoring

**User Story:** As a team leader, I want to see the health status of each component, so that I can quickly identify processes that need attention.

#### Acceptance Criteria

1. THE System SHALL support three Health_Status values: smooth, on_fire, and needs_love
2. WHEN a Component has Health_Status smooth, THE System SHALL display a ✅ indicator
3. WHEN a Component has Health_Status on_fire, THE System SHALL display a 🔥 indicator
4. WHEN a Component has Health_Status needs_love, THE System SHALL display a 💛 indicator
5. WHEN a User views the Dashboard, THE System SHALL display Health_Status for all Components
6. THE System SHALL allow Users to update a Component's Health_Status
7. WHEN a Component's Health_Status changes, THE System SHALL persist the new status immediately

### Requirement 4: Upgrade Shipping and Activation

**User Story:** As a process owner, I want to ship upgrades to activate them, so that the latest version of the workflow becomes the active procedure.

#### Acceptance Criteria

1. WHEN a User ships an Upgrade, THE System SHALL mark the Upgrade as active
2. WHEN a User ships an Upgrade, THE System SHALL record the ship timestamp
3. WHEN a User ships an Upgrade, THE System SHALL create a notification event
4. WHEN multiple Upgrades exist for a Component, THE System SHALL allow only one to be active at a time
5. WHEN a User ships a new Upgrade, THE System SHALL deactivate the previously active Upgrade for that Component
6. THE System SHALL display the active Upgrade prominently in the Component view

### Requirement 5: Quick Ship Interface

**User Story:** As a busy manager, I want quick access to components needing attention, so that I can efficiently address process issues.

#### Acceptance Criteria

1. THE System SHALL provide a Quick_Ship dropdown interface
2. WHEN a User opens Quick_Ship, THE System SHALL display all Components with Health_Status on_fire
3. WHEN a User opens Quick_Ship, THE System SHALL display all Components with Health_Status needs_love
4. THE System SHALL exclude Components with Health_Status smooth from Quick_Ship
5. WHEN a User selects a Component from Quick_Ship, THE System SHALL navigate to that Component's detail view
6. THE Quick_Ship SHALL be accessible from any page in the System

### Requirement 6: Streak Tracking System

**User Story:** As a continuous improvement advocate, I want to track weekly upgrade shipping streaks, so that I can encourage consistent process improvement habits.

#### Acceptance Criteria

1. THE System SHALL track the number of consecutive weeks with at least one shipped Upgrade
2. WHEN a User ships an Upgrade in the current week, THE System SHALL increment or maintain the streak
3. WHEN no Upgrades are shipped in a week, THE System SHALL reset the streak to zero
4. THE System SHALL display the current streak count on the Dashboard
5. WHEN calculating streaks, THE System SHALL use week boundaries (Sunday to Saturday or Monday to Sunday)
6. THE System SHALL persist streak data across user sessions

### Requirement 7: Notification System

**User Story:** As a team member, I want to receive notifications about upgrade events, so that I stay informed about process changes.

#### Acceptance Criteria

1. WHEN an Upgrade is shipped, THE System SHALL create a notification
2. WHEN an Upgrade is created, THE System SHALL create a notification
3. WHEN an Upgrade is edited, THE System SHALL create a notification
4. THE System SHALL display notifications in a notification center
5. WHEN a User views a notification, THE System SHALL mark it as read
6. THE System SHALL include the Upgrade name, Component name, and action type in each notification
7. THE System SHALL order notifications by timestamp with most recent first

### Requirement 8: Dashboard Overview

**User Story:** As an executive, I want a dashboard showing all machines and their health, so that I can understand the overall state of our processes at a glance.

#### Acceptance Criteria

1. THE System SHALL display all Machines on the Dashboard
2. WHEN a User views the Dashboard, THE System SHALL show health metrics for each Machine
3. THE System SHALL calculate Machine health based on child Component Health_Status values
4. WHEN a User clicks a Machine on the Dashboard, THE System SHALL navigate to the Machine detail view
5. THE Dashboard SHALL display the current streak count
6. THE Dashboard SHALL be the default landing page after login

### Requirement 9: Search and Discovery

**User Story:** As a user, I want to search for workflows and processes, so that I can quickly find the information I need.

#### Acceptance Criteria

1. THE System SHALL provide a search interface accessible from all pages
2. WHEN a User enters a search query, THE System SHALL search across Machine names
3. WHEN a User enters a search query, THE System SHALL search across Subsystem names
4. WHEN a User enters a search query, THE System SHALL search across Component names
5. WHEN a User enters a search query, THE System SHALL search across Upgrade names and content
6. THE System SHALL display search results grouped by entity type
7. WHEN a User selects a search result, THE System SHALL navigate to that entity's detail view
8. THE System SHALL support partial text matching in search queries

### Requirement 10: Upgrade Version History

**User Story:** As a process auditor, I want to see the history of upgrades for a component, so that I can track how processes have evolved over time.

#### Acceptance Criteria

1. WHEN a User views a Component, THE System SHALL display all Upgrades associated with that Component
2. THE System SHALL order Upgrades by ship date with most recent first
3. WHEN an Upgrade has been shipped, THE System SHALL display the ship timestamp
4. WHEN an Upgrade has not been shipped, THE System SHALL indicate it as a draft
5. THE System SHALL allow Users to view details of any historical Upgrade
6. THE System SHALL preserve all Upgrade data even after new Upgrades are shipped

### Requirement 11: User Authentication and Authorization

**User Story:** As a system administrator, I want to control who can access and modify workflows, so that I can maintain data integrity and security.

#### Acceptance Criteria

1. THE System SHALL require Users to authenticate before accessing any functionality
2. WHEN a User attempts to access the System without authentication, THE System SHALL redirect to the login page
3. THE System SHALL maintain user sessions across page navigations
4. WHEN a User logs out, THE System SHALL terminate the session and redirect to the login page
5. THE System SHALL support role-based access control for different user types
6. THE System SHALL allow administrators to create and manage user accounts

### Requirement 12: Workflow Dependencies

**User Story:** As a process architect, I want to define dependencies between components, so that I can represent workflows that depend on other workflows.

#### Acceptance Criteria

1. THE System SHALL allow Users to define prerequisite relationships between Components
2. WHEN a User defines a dependency, THE System SHALL record both the dependent and prerequisite Component
3. WHEN a User views a Component, THE System SHALL display all prerequisite Components
4. WHEN a User views a Component, THE System SHALL display all dependent Components
5. THE System SHALL prevent circular dependencies between Components
6. WHEN a Component has unmet prerequisites, THE System SHALL indicate this in the Component view

### Requirement 13: Workflow Templates

**User Story:** As a process creator, I want to use templates for common workflow types, so that I can quickly create standardized upgrades.

#### Acceptance Criteria

1. THE System SHALL provide predefined templates for common Upgrade types
2. WHEN a User creates an Upgrade, THE System SHALL offer template selection
3. WHEN a User selects a template, THE System SHALL pre-populate the Upgrade fields with template content
4. THE System SHALL allow Users to modify template content after selection
5. THE System SHALL allow administrators to create custom templates
6. THE System SHALL allow administrators to edit existing templates

### Requirement 14: Analytics and Reporting

**User Story:** As a business analyst, I want to generate reports on process improvement activities, so that I can measure our continuous improvement efforts.

#### Acceptance Criteria

1. THE System SHALL track the total number of Upgrades shipped per Machine
2. THE System SHALL track the total number of Upgrades shipped per Subsystem
3. THE System SHALL track the total number of Upgrades shipped per Component
4. THE System SHALL calculate the average time between Upgrade ships for each Component
5. WHEN a User requests a report, THE System SHALL generate a summary of shipping activity
6. THE System SHALL display trends in Health_Status changes over time
7. THE System SHALL allow Users to export report data in CSV format

### Requirement 15: Mobile Responsiveness

**User Story:** As a field worker, I want to access workflows on my mobile device, so that I can reference procedures while performing tasks.

#### Acceptance Criteria

1. WHEN a User accesses the System on a mobile device, THE System SHALL display a mobile-optimized layout
2. THE System SHALL maintain full functionality on mobile devices
3. WHEN a User views the Dashboard on mobile, THE System SHALL use a single-column layout
4. WHEN a User views an Upgrade on mobile, THE System SHALL display all fields in a readable format
5. THE System SHALL support touch gestures for navigation on mobile devices
6. THE System SHALL load efficiently on mobile network connections

### Requirement 16: Workflow Execution Tracking

**User Story:** As a process executor, I want to track when I execute a workflow, so that we can measure actual usage and compliance.

#### Acceptance Criteria

1. THE System SHALL allow Users to mark an Upgrade as executed
2. WHEN a User marks an Upgrade as executed, THE System SHALL record the execution timestamp
3. WHEN a User marks an Upgrade as executed, THE System SHALL record which User performed the execution
4. THE System SHALL display execution history for each Upgrade
5. THE System SHALL calculate execution frequency for each Upgrade
6. WHEN a Component has low execution frequency, THE System SHALL flag it for review

### Requirement 17: Collaboration and Comments

**User Story:** As a team member, I want to comment on upgrades, so that I can provide feedback and ask questions about workflows.

#### Acceptance Criteria

1. THE System SHALL allow Users to add comments to any Upgrade
2. WHEN a User adds a comment, THE System SHALL record the comment text, author, and timestamp
3. THE System SHALL display all comments for an Upgrade in chronological order
4. THE System SHALL allow Users to edit their own comments
5. THE System SHALL allow Users to delete their own comments
6. WHEN a User adds a comment, THE System SHALL notify relevant stakeholders
7. THE System SHALL support @mentions to tag specific Users in comments

### Requirement 18: Bulk Operations

**User Story:** As an administrator, I want to perform bulk operations on multiple entities, so that I can efficiently manage large numbers of workflows.

#### Acceptance Criteria

1. THE System SHALL allow Users to select multiple Components using checkboxes
2. WHEN multiple Components are selected, THE System SHALL display bulk action options
3. THE System SHALL support bulk Health_Status updates for selected Components
4. THE System SHALL support bulk deletion of selected Components
5. THE System SHALL support bulk export of selected Components
6. WHEN a bulk operation is performed, THE System SHALL display a confirmation dialog
7. WHEN a bulk operation completes, THE System SHALL display a success message with the count of affected entities

### Requirement 19: Workflow Import and Export

**User Story:** As a process manager, I want to export and import workflows, so that I can share processes between teams or backup my data.

#### Acceptance Criteria

1. THE System SHALL allow Users to export a Machine and all its children to JSON format
2. THE System SHALL allow Users to export a Subsystem and all its children to JSON format
3. THE System SHALL allow Users to export a Component and all its Upgrades to JSON format
4. THE System SHALL allow Users to import workflow data from JSON files
5. WHEN importing workflows, THE System SHALL validate the JSON structure
6. WHEN importing workflows, THE System SHALL prevent duplicate entity creation
7. THE System SHALL provide clear error messages for invalid import data

### Requirement 20: Audit Trail

**User Story:** As a compliance officer, I want to see a complete audit trail of all changes, so that I can ensure accountability and track modifications.

#### Acceptance Criteria

1. THE System SHALL record all create, update, and delete operations
2. WHEN an entity is modified, THE System SHALL record the User who made the change
3. WHEN an entity is modified, THE System SHALL record the timestamp of the change
4. WHEN an entity is modified, THE System SHALL record the fields that were changed
5. THE System SHALL display audit history for any entity
6. THE System SHALL allow filtering audit logs by User, date range, and entity type
7. THE System SHALL prevent modification or deletion of audit log entries
