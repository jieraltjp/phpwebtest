## ADDED Requirements
### Requirement: 用户认证
系统 SHALL 提供基于 JWT 的用户认证功能，支持雅虎客户登录并获取访问令牌。

#### Scenario: 成功登录
- **WHEN** 用户提供有效的用户名和密码
- **THEN** 系统返回 JWT 访问令牌和令牌类型
- **AND** 令牌可用于后续 API 请求认证

#### Scenario: 登录失败
- **WHEN** 用户提供无效的凭据
- **THEN** 系统返回认证错误信息
- **AND** 不返回任何令牌信息

### Requirement: 令牌验证
系统 SHALL 验证每个受保护 API 请求中的 JWT 令牌有效性。

#### Scenario: 有效令牌访问
- **WHEN** 请求包含有效的 JWT 令牌
- **THEN** 系统允许访问受保护的 API 资源

#### Scenario: 无效令牌访问
- **WHEN** 请求包含无效或过期的 JWT 令牌
- **THEN** 系统拒绝访问并返回认证错误