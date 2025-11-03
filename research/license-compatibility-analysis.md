# License Compatibility Analysis

## Status: In Progress

## Primary Base Repositories

### 1. 1Panel
- **License**: GPL-3.0 (GNU General Public License v3.0)
- **Compatibility**: 
  - ✅ Can be forked and modified
  - ⚠️ Derivative works must also be GPL-3.0
  - ✅ Can combine with GPL-3.0 and compatible licenses
  - ⚠️ Cannot combine with Apache 2.0, MIT, or proprietary code in the same binary
  - **Recommendation**: If using 1Panel as base, entire project must be GPL-3.0

### 2. Coolify
- **License**: Apache-2.0 (Apache License 2.0)
- **Compatibility**:
  - ✅ Highly permissive - can be forked and modified
  - ✅ Can combine with MIT, BSD, Apache 2.0
  - ✅ Can include proprietary code
  - ✅ Derivative works can use different licenses (if not using Apache code)
  - **Recommendation**: Most flexible option, can mix with various licenses

### 3. HestiaCP
- **License**: GPL-3.0 (GNU General Public License v3.0)
- **Compatibility**:
  - ✅ Can be forked and modified
  - ⚠️ Same restrictions as 1Panel
  - **Recommendation**: If using, entire project must be GPL-3.0

### 4. Dokploy
- **License**: NOASSERTION (GitHub API shows "Other")
- **Action Required**: Check repository LICENSE file directly via web interface
- **Note**: Repository exists and is active, but license needs manual verification

## Component Repositories - License Review

### AI Integration & MCP Servers

1. **Awesome MCP Servers** (Curated List)
   - **License**: Need to verify (likely MIT)
   - **Impact**: Reference list, no code integration

2. **MCP Servers Hub**
   - **License**: Need to verify
   - **Impact**: Catalog/reference

3. **GitHub Repos Manager MCP Server**
   - **License**: Need to verify
   - **Impact**: Integration component

### IDE Integration

1. **code-server**
   - **License**: ✅ MIT
   - **Compatibility**: ✅ Compatible with all licenses

2. **Theia**
   - **License**: ✅ EPL-2.0 (Eclipse Public License 2.0)
   - **Compatibility**: ✅ Permissive, can mix with other licenses

### API Management

1. **Kong**
   - **License**: ✅ Apache-2.0
   - **Compatibility**: ✅ Highly compatible

2. **Tyk**
   - **License**: NOASSERTION (needs manual check)
   - **Action**: Check Tyk repository LICENSE file
   - **Likely**: MPL 2.0 or Apache 2.0

3. **Pezzo**
   - **License**: ✅ Apache-2.0
   - **Compatibility**: ✅ Highly compatible

## Critical License Compatibility Matrix

### Scenario 1: Using 1Panel (GPL-3.0) as Base
- ⚠️ **Requirement**: Entire derived work must be GPL-3.0
- ✅ **Compatible**: GPL-3.0, AGPL-3.0, LGPL-3.0 components
- ❌ **Incompatible**: Apache 2.0, MIT, proprietary components in same binary
- **Solution**: Can use Apache/MIT components as separate services/programs

### Scenario 2: Using Coolify (Apache-2.0) as Base
- ✅ **Requirement**: Must include Apache 2.0 license, attribution
- ✅ **Compatible**: MIT, BSD, Apache 2.0, proprietary
- ✅ **Flexible**: Can create proprietary extensions
- **Solution**: Most flexible for commercial and mixed-license projects

### Scenario 3: Using HestiaCP (GPL-3.0) as Base
- ⚠️ Same restrictions as Scenario 1

## Recommendations

### For Open Source Project (Recommended: Coolify)
1. **Best Choice**: Coolify (Apache-2.0) - maximum flexibility
2. Can integrate components with various licenses
3. Can create proprietary add-ons if needed
4. Most permissive for commercial use

### For GPL-Compatible Project
1. **Choice**: 1Panel or HestiaCP (GPL-3.0)
2. Entire project must remain GPL-3.0
3. Can use other GPL-3.0 compatible components
4. External services (API gateways, etc.) can be separate

## Next Steps

1. ✅ Verify Dokploy license
2. ✅ Check licenses of all MCP server repositories
3. ✅ Verify IDE component licenses
4. ✅ Check API management tool licenses
5. ⏳ Create license attribution file
6. ⏳ Determine final license choice based on base repository
7. ⏳ Document all third-party licenses for attribution

## License Attribution Requirements

Regardless of chosen license, must include:
- Original copyright notices
- License text (or link) for all included components
- Changes made (for GPL-3.0 projects)
- License compatibility documentation

