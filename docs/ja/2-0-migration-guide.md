# 2.0 Migration Guide

Authorization 2.0 には新しい機能と破壊的変更が含まれています。

## 破壊的変更

`IdentityInterface` に型宣言が追加されました。アプリケーションで実装して
いる場合は、シグネチャを更新してください。

`canResult()` も追加され、常に `ResultInterface` を返すようになりました。
`can()` は常に bool を返します。
