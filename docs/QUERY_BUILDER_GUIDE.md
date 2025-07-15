# Spatie Laravel Query Builder Guide

This guide shows how to use the powerful query builder features in your Laravel API.

## Available Endpoints

### 1. **GET /api/users** - List all users with filtering, sorting, and pagination

#### Basic Usage
```bash
GET /api/users
```

#### Filtering Examples
```bash
# Filter by name (partial match)
GET /api/users?filter[name]=john

# Filter by email (partial match)
GET /api/users?filter[email]=gmail.com

# Filter by exact ID
GET /api/users?filter[id]=1

# Filter by creation date range
GET /api/users?filter[created_after]=2024-01-01&filter[created_before]=2024-12-31

# Filter by role
GET /api/users?filter[has_role]=admin

# Filter by permission
GET /api/users?filter[has_permission]=edit-users
```

#### Sorting Examples
```bash
# Sort by name (ascending)
GET /api/users?sort=name

# Sort by name (descending)
GET /api/users?sort=-name

# Sort by creation date (descending) - this is the default
GET /api/users?sort=-created_at

# Sort by multiple fields
GET /api/users?sort=name,-created_at
```

#### Including Relationships
```bash
# Include roles
GET /api/users?include=roles

# Include permissions
GET /api/users?include=permissions

# Include roles and permissions
GET /api/users?include=roles,permissions

# Include roles with their permissions
GET /api/users?include=roles.permissions
```

#### Pagination
```bash
# Custom page size
GET /api/users?page[size]=10

# Specific page
GET /api/users?page[number]=2

# Combined with filtering and sorting
GET /api/users?filter[name]=john&sort=-created_at&page[size]=5&page[number]=1
```

#### Complex Query Examples
```bash
# Find all admin users created this year, sorted by name, with roles included
GET /api/users?filter[has_role]=admin&filter[created_after]=2024-01-01&sort=name&include=roles

# Find users with gmail emails, include their permissions, paginated
GET /api/users?filter[email]=gmail.com&include=permissions&page[size]=10
```

### 2. **GET /api/users/{id}** - Get specific user with optional includes

```bash
# Get user with ID 1
GET /api/users/1

# Get user with roles and permissions
GET /api/users/1?include=roles,permissions
```

### 3. **GET /api/users/with-roles** - Get users filtered by roles

```bash
# Get all admin users
GET /api/users/with-roles?filter[role]=admin

# Get users with multiple roles
GET /api/users/with-roles?filter[roles]=admin,moderator

# Include role details
GET /api/users/with-roles?filter[role]=admin&include=roles
```

## Response Format

### User List Response
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "email_verified_at": "2024-01-15T10:30:00.000000Z",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z",
      "roles": [
        {
          "id": 1,
          "name": "admin",
          "guard_name": "web",
          "permissions": [
            {
              "id": 1,
              "name": "edit-users",
              "guard_name": "web"
            }
          ]
        }
      ],
      "permissions": [
        {
          "id": 1,
          "name": "edit-users",
          "guard_name": "web"
        }
      ]
    }
  ],
  "links": {
    "first": "/api/users?page=1",
    "last": "/api/users?page=10",
    "prev": null,
    "next": "/api/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "path": "/api/users",
    "per_page": 15,
    "to": 15,
    "total": 150
  }
}
```

## Security Features

### Whitelisted Filters
Only these filters are allowed:
- `name` (partial match)
- `email` (partial match)
- `id` (exact match)
- `created_after` (date scope)
- `created_before` (date scope)
- `has_role` (role scope)
- `has_permission` (permission scope)

### Whitelisted Sorts
Only these sorts are allowed:
- `name`
- `email`
- `created_at`
- `updated_at`
- `latest` (alias for `created_at`)

### Whitelisted Includes
Only these relationships can be included:
- `roles`
- `permissions`
- `roles.permissions`

## Frontend Integration Examples

### JavaScript/Fetch
```javascript
// Get users with filtering and sorting
const response = await fetch('/api/users?filter[name]=john&sort=-created_at&include=roles', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

const users = await response.json();
```

### Vue.js Example
```vue
<template>
  <div>
    <input v-model="filters.name" placeholder="Search by name" />
    <select v-model="sort">
      <option value="name">Name (A-Z)</option>
      <option value="-name">Name (Z-A)</option>
      <option value="-created_at">Newest First</option>
      <option value="created_at">Oldest First</option>
    </select>
    
    <div v-for="user in users" :key="user.id">
      {{ user.name }} - {{ user.email }}
      <span v-if="user.roles">
        ({{ user.roles.map(r => r.name).join(', ') }})
      </span>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      users: [],
      filters: {
        name: '',
        email: ''
      },
      sort: '-created_at',
      includes: ['roles']
    }
  },
  watch: {
    filters: {
      handler: 'fetchUsers',
      deep: true
    },
    sort: 'fetchUsers'
  },
  methods: {
    async fetchUsers() {
      const params = new URLSearchParams();
      
      // Add filters
      Object.entries(this.filters).forEach(([key, value]) => {
        if (value) params.append(`filter[${key}]`, value);
      });
      
      // Add sort
      if (this.sort) params.append('sort', this.sort);
      
      // Add includes
      if (this.includes.length) params.append('include', this.includes.join(','));
      
      const response = await fetch(`/api/users?${params}`, {
        headers: {
          'Authorization': `Bearer ${this.$store.state.token}`,
          'Accept': 'application/json'
        }
      });
      
      const data = await response.json();
      this.users = data.data;
    }
  }
}
</script>
```

## Benefits

1. **Frontend Flexibility**: Frontend can request exactly what it needs
2. **Performance**: Only loads requested relationships
3. **Security**: Whitelist system prevents unauthorized queries
4. **Consistency**: Standardized query parameter format
5. **Scalability**: Built-in pagination and efficient queries
6. **Developer Experience**: Clear, predictable API structure

## Best Practices

1. **Always whitelist** allowed filters, sorts, and includes
2. **Use scopes** for complex filtering logic
3. **Implement pagination** for large datasets
4. **Include only what you need** to avoid over-fetching
5. **Use partial filters** for user-friendly search
6. **Provide default sorting** for consistent results 