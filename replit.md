# Shake-Out Payment Gateway for Moodle

## Overview

This project is a Moodle payment gateway plugin that integrates the Shake-Out payment service. It provides a complete payment processing solution within the Moodle Learning Management System, allowing users to make payments for courses, activities, or other paid content through the Shake-Out payment platform. The plugin follows Moodle's standard payment gateway architecture and includes modal-based user interfaces for seamless payment processing.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **AMD Module System**: Uses Moodle's AMD (Asynchronous Module Definition) pattern for JavaScript modules
- **Modal-Based UI**: Implements payment interface through Moodle's modal factory system for consistent user experience
- **Template Rendering**: Leverages Moodle's template system for dynamic content generation
- **Event-Driven Architecture**: Uses Moodle's modal events system for handling user interactions

### Backend Integration
- **AJAX Repository Pattern**: Implements repository pattern for backend communication through Moodle's Ajax framework
- **Web Service Architecture**: Utilizes Moodle's web service system for secure API calls to payment status endpoints
- **Component-Based Structure**: Follows Moodle's component architecture with proper namespacing and modular design

### Payment Processing Flow
- **Modal-Triggered Payments**: Payment process initiated through modal dialogs with save/cancel options
- **Status Polling**: Implements payment status checking through dedicated repository methods
- **Configuration Validation**: Includes validation mechanisms for payment gateway configuration

### Security and Compliance
- **GPL v3 Licensing**: Adheres to Moodle's open-source licensing requirements
- **Moodle Security Standards**: Follows Moodle's security practices for payment handling
- **Parameter Validation**: Implements proper parameter validation for all payment-related operations

## External Dependencies

### Moodle Core Dependencies
- **Core String API**: For internationalization and language string management
- **Core Templates**: For rendering payment interface templates
- **Modal Factory**: For creating consistent modal dialogs
- **Modal Events**: For handling modal interactions
- **Core Notifications**: For user feedback and error handling
- **Core Ajax**: For secure backend communication

### Payment Service Integration
- **Shake-Out Payment API**: External payment processing service for handling transactions
- **Invoice Management**: Integration with Shake-Out's invoice and payment status tracking system

### Moodle Payment Subsystem
- **Payment Gateway Framework**: Built on Moodle's standardized payment gateway architecture
- **Payment Areas**: Integrates with various Moodle components that support payments (courses, activities, etc.)
- **Payment Configuration**: Utilizes Moodle's payment configuration management system