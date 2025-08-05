# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ThinkAI is a PHP library that provides a unified interface for multiple AI services (GPT, DeepSeek, 智谱, etc.) through the ThinkAI platform. It's designed for the ThinkPHP ecosystem but can be used independently.

## Key Commands

### Development Setup
```bash
# Install dependencies
composer install

# Update dependencies
composer update

# Regenerate autoloader
composer dump-autoload
```

### Common Tasks
- **Adding a new API endpoint**: Create a new class in `src/api/` extending `think\ai\Api`
- **Testing API calls**: Use the Client class with appropriate API key and endpoint

Note: This project currently lacks testing infrastructure. Consider adding PHPUnit when implementing new features.

## Architecture

### Core Components
- **Client** (`src/Client.php`): Main entry point, provides factory methods for all API services
- **Api** (`src/Api.php`): Abstract base class for all API endpoints, handles HTTP requests and responses
- **StreamIterator** (`src/StreamIterator.php`): Handles streaming responses from AI services

### API Structure
Each API service has its own class in `src/api/`:
- Chat completions, image generation, audio processing, embeddings, etc.
- All extend the base Api class and implement specific endpoint logic
- Methods typically accept arrays of parameters and return arrays or StreamIterator for streaming

### Design Patterns
- Factory pattern in Client class for creating API instances
- Strategy pattern for different API implementations
- Iterator pattern for streaming responses

## Development Guidelines

### When Adding New Features
1. Follow existing namespace convention: `think\ai\api\{ServiceName}`
2. Extend the base Api class for new endpoints
3. Use typed properties and return types (PHP 8.0+)
4. Handle both streaming and non-streaming responses where applicable

### Error Handling
- Use the custom `think\ai\Exception` for API-related errors
- Include meaningful error messages with context
- Check for required parameters before making API calls

### Code Style
- Follow PSR-4 autoloading standards
- Use modern PHP features (constructor property promotion, typed properties)
- Keep methods focused and single-purpose

## Important Notes

- The project uses Guzzle HTTP client for all API requests
- Authentication is handled via Bearer tokens passed to the Client constructor
- All API responses are automatically JSON decoded
- Streaming responses return a StreamIterator that can be used with foreach
- The library supports multiple AI providers through a unified interface